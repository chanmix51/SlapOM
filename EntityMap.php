<?php
namespace SlapOM;

use SlapOM\Exception\SlapOm as SlapOMException;

abstract class EntityMap
{
    protected $connection;
    protected $base_dn;
    protected $ldap_object_class;
    protected $entity_class;
    protected $attributes = array();

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

        if (count($this->attributes) == 0)
        {
            throw new SlapOMException(sprintf("Attributes list is empty after configured class '%s'.", get_class($this)));
        }

    }

    abstract protected function configure();

    public function find($filter, $dn_suffix = null, $limit = 0)
    {
        $dn = is_null($dn_suffix) ? $this->base_dn : $this->base_dn.",".$dn_suffix;

        $results = $this->connection->search($dn, $filter, $this->getAttributes(), $limit);

        $entity_class = $this->entity_class;

        return new ArrayIterator(array_map($results, function($val) use ($entity_class) { return new $entity_class($val); }));
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
