<?php
namespace SlapOM;

use SlapOM\Exception\SlapOM as SlapOMException;

abstract class EntityMap
{
    protected $connection;
    protected $base_dn;
    protected $ldap_object_class;
    protected $entity_class;
    protected $attributes = array('dn');

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

    public function find($filter, $dn_suffix = null, $limit = 0)
    {
        $dn = is_null($dn_suffix) ? $this->base_dn : $this->base_dn.",".$dn_suffix;
        $filter = sprintf("(&(objectClass=%s)%s)", $this->ldap_object_class, $filter);

        $results = $this->connection->search($dn, $filter, $this->getAttributes(), $limit);

        $entity_class = $this->entity_class;
        $entities = array();

            var_dump($results); exit;
        foreach ($results as $result)
        {
            $entities[] = new $entity_class($result);
        }

        return new \ArrayIterator($entities);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function addAtribute($name)
    {
        $this->attributes[] = $name;
    }
}
