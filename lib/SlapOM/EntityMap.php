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
    protected $attributes = array('dn' => 0);
    protected $read_only_attributes = array('dn', 'objectclass');

    public final function __construct(\SlapOM\Connection $connection)
    {
        $this->connection = $connection;
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

    public function find($filter = null, $dn_suffix = null, $limit = 0)
    {
        $dn = is_null($dn_suffix) ? $this->base_dn : $dn_suffix.",".$this->base_dn;

        if (is_null($filter))
        {
            $filter = sprintf("(&(objectClass=%s))", $this->ldap_object_class, $filter);
        }
        else
        {
            $filter = sprintf("(&(objectClass=%s)%s)", $this->ldap_object_class, $filter);
        }

        $results = $this->connection->search($dn, $filter, $this->getAttributeNames(), $limit);

        return $this->processResults($results);
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
        return $this->attributes[$name];
    }

    public function save(\SlapOM\Entity $entity)
    {
        if (false === isset($entity['dn']))
        {
            Throw new SlapOMException("This fonctionality is not yet implemented.");
        }
        
        $entry = array();
        
        foreach ($this->getAttributeNames() as $attr)
        {
            if (false === in_array($attr, $this->read_only_attributes))
            {
                $entry[$attr] = $entity[$attr];
            }
        }
        $this->connection->modify($entity->getDn(), $entry);

        $entity->persist();
    }

    protected function processResults($results)
    {
        $entity_class = $this->entity_class;
        $entities = array();

        if ($results['count'] > 0)
        {
            unset($results['count']);
            // iterate on results
            foreach ($results as $result)
            {
                array_walk($result, array($this, 'processFieldValue'));

                $entities[] = new $entity_class($result);
            }
        }

        return new \ArrayIterator($entities);
    }

    protected function processFieldValue(&$value, $field)
    {
        if (is_array($value))
        {
            unset($value['count']);

            if (!$this->getAttributeModifiers($field) & static::FIELD_MULTIVALUED)
            {
                $value = array_shift($value);
            }
        }
    }

}
