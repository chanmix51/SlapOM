<?php
namespace SlapOM;

use SlapOM\Exception\SlapOM as SlapOMException;

abstract class Entity extends \ArrayObject
{
    const ENTITY_NEW = 0;
    const ENTITY_MODIFIED = 1;
    const ENTITY_PERSISTED = 2;

    protected $state = self::ENTITY_NEW;

    public function _getState()
    {
        return $this->state;
    }

    public function _setState($state)
    {
        $this->state = $state;
    }

    public function modify()
    {
        $this->state = $this->state | self::ENTITY_MODIFIED;
    }

    public function isModified()
    {
        return (bool) $this->state & self::ENTITY_MODIFIED;
    }

    public function persist()
    {
        $this->state = $this->state | self::ENTITY_PERSISTED;
    }

    public function isPersisted()
    {
        $this->state = $this->state & self::ENTITY_PERSISTED;
    }

    public function isNew()
    {
        $this->state = $this->state & self::ENTITY_NEW;
    }


    /**
     * __call
     *
     * Allows dynamic methods getXxx, setXxx, hasXxx, addXxx or clearXxx.
     *
     * @param mixed $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        list($operation, $attribute) = preg_split('/(?=[A-Z])/', $method, 2);
        $attribute = StrUtil::underscore($attribute);

        switch($operation)
        {
        case 'set':
            return $this->offsetSet($attribute, $arguments[0]);
        case 'get':
            return $this->offsetGet($attribute);
        case 'has':
            return $this->offsetExists($attribute);
        case 'clear':
            return $this->offsetUnset($attribute);
        default:
            throw new SlapOMException(sprintf('No such method "%s:%s()"', get_class($this), $method));
        }
    }
}
