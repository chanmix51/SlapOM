<?php
namespace SlapOM;

use SlapOM\Exception\SlapOM as SlapOMException;

abstract class EntityMap
{
    const FIELD_MULTIVALUED = 1;
    const FIELD_BINARY      = 2;

    protected $connection;
    protected $base_dn;
    protected $ldap_object_class;
    protected $entity_class;
    protected $attributes;
    protected $read_only_attributes = array('dn', 'objectClass');

    public final function __construct(\SlapOM\Connection $connection)
    {
        $this->connection = $connection;
        $this->attributes =  array('dn' => 0);
        $this->configure();

        if (!isset($this->base_dn))
        {
            throw new SlapOMException(sprintf("Base DN is not set after configured class '%s'.", get_class($this)));
        }

        if (!isset($this->ldap_object_class))
        {
            throw new SlapOMException(sprintf("LDAP 'objectClass' is not set after configured class '%s'.", get_class($this)));
        }

        if (!isset($this->entity_class))
        {
            throw new SlapOMException(sprintf("Entity class is not set after configured class '%s'.", get_class($this)));
        }

        if (count($this->attributes) <= 1)
        {
            throw new SlapOMException(sprintf("Attributes list is empty after configured class '%s'.", get_class($this)));
        }

    }

    abstract protected function configure();

    public function createObject(Array $values)
    {
        $class_name = $this->entity_class;

        return new $class_name($values);
    }

    public function find($filter = null, $dn_suffix = null, $limit = 0)
    {
        $dn = is_null($dn_suffix) ? $this->base_dn : $dn_suffix.",".$this->base_dn;

        if (is_null($filter))
        {
            $filter = $this->getObjectClassFilter();
        }
        else
        {
            $filter = sprintf("(&%s%s)", $this->getObjectClassFilter(), $filter);
        }

        $collection = $this->connection->search($this, $dn, $filter, $this->getSearchFields(), $limit);

        return $collection;
    }

    public function fetch($dn)
    {
        $this->checkDn($dn);
        $collection = $this->connection->search($this, $dn, $this->getObjectClassFilter(), $this->getSearchFields());

        return $collection->current();
    }

    public function getAttributeNames()
    {
        return array_keys($this->attributes);
    }

    public function addAttribute($name, $modifier = 0)
    {
        $this->attributes[$name] = $modifier;
    }

    public function getAttributeModifiers($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function save(\SlapOM\Entity $entity, Array $attributes = null)
    {
        if (false === isset($entity['dn']))
        {
            Throw new SlapOMException("The create feature is not yet implemented.");
        }

        $attributes = is_null($attributes) ? $this->getAttributeNames() : array_intersect($this->getAttributeNames(), $attributes);
        $attributes = array_diff($attributes, $this->read_only_attributes);
        $entry = array();

        foreach ($attributes as $attr)
        {
            $entry[$attr] = $entity[$attr];
        }

        $this->connection->modify($entity->getDn(), $entry);
        $entity->persist();
    }

    public function getSearchFields()
    {
        return $this->getAttributeNames();
    }

    protected function getObjectClassFilter()
    {
      return sprintf("(objectClass=%s)", $this->ldap_object_class);
    }

    protected function checkDn($dn)
    {
        if (strpos($dn, $this->base_dn) === false)
        {
            throw new SlapOMException(sprintf("Given dn='%s' is not compatible with class base db '%s'.", $dn, $this->base_dn));
        }
    }
}
