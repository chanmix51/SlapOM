<?php
namespace SlapOM;

use SlapOM\Exception\SlapOM as SlapOMException;

abstract class Entity implements \ArrayAccess
{
    const ENTITY_NEW = 0;
    const ENTITY_MODIFIED = 1;
    const ENTITY_PERSISTED = 2;

    protected $state = self::ENTITY_NEW;
    protected $values = array();

    public function __construct(Array $values = array())
    {
        $this->values = $values;
    }

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
        return (bool) ($this->state & self::ENTITY_MODIFIED);
    }

    public function persist()
    {
        $this->state = $this->state | self::ENTITY_PERSISTED;
    }

    public function isPersisted()
    {
        return (bool) ($this->state & self::ENTITY_PERSISTED);
    }

    public function isNew()
    {
        return ($this->_getState() === self::ENTITY_NEW);
    }


    public function get($name)
    {
        if (!$this->has($name))
        {
            throw new SlapOMException(sprintf("Could not GET non existant field '%s'.", $name));
        }

        return $this->values[$name];
    }

    public function set($name, $attribute)
    {
        $this->values[$name] = $attribute;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->values);
    }

    public function clear($name)
    {
        if (!$this->has($name))
        {
            throw new SlapOMException(sprintf("Could not CLEAR non existant field '%s'.", $name));
        }

        unset($this->values[$name]);
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
        $attribute = strtolower($attribute[0]).substr($attribute, 1);

        switch($operation)
        {
        case 'set':
            $this->set($attribute, $arguments[0]);
            $this->modify();
        case 'get':
            return $this->get($attribute);
        case 'has':
            return $this->has($attribute);
        case 'clear':
            return parent::offsetUnset($attribute);
        default:
            throw new SlapOMException(sprintf('No such method "%s:%s()"', get_class($this), $method));
        }
    }

    public function offsetGet($name)
    {
        $method = sprintf("get%s", TextUtils::camelize($name));

        return $this->$method();
    }

    public function offsetExists($name)
    {
        $method = sprintf("has%s", TextUtils::camelize($name));

        return $this->$method();
    }

    public function offsetSet($name, $value)
    {
        $method = sprintf("set%s", TextUtils::camelize($name));

        return $this->$method($value);
    }

    public function offsetUnset($name)
    {
        $method = sprintf("clear%s", TextUtils::camelize($name));

        return $this->$method($name);
    }

    public function export()
    {
        $values = array();
        foreach($this->values as $key => $value)
        {
            $methodGet = sprintf("get%s", TextUtils::camelize($key));
            $methodHas = sprintf("has%s", TextUtils::camelize($key));

            if ($this->$methodHas($key))
            {
                $values[$key] = $this->$methodGet();
            }
        }

        return $values;
    }
}
