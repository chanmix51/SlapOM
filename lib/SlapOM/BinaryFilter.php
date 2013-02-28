<?php
namespace SlapOM;

class BinaryFilter
{
    public $right;
    public $left;
    public $operator;

    static public function create($elt = null)
    {
        return new BinaryFilter($elt);
    }

    public function __construct($elt = null)
    {
        $this->right = $elt;
    }

    public function setOperator($op)
    {
        $this->operator = $op;

        return $this;
    }

    public function isEmpty()
    {
        return (is_null($this->right) and is_null($this->left));
    }

    public function isPartial()
    {
        return (!is_null($this->right) and is_null($this->left));
    }

    public function isComplete()
    {
        return (!is_null($this->right) and !is_null($this->left));
    }

    public function addFilter($elt, $operator = null)
    {
        if (is_array($elt))
        {
            return array_reduce($elt, function($filter, $element) use ($operator) { return $filter->addFilter($element, $operator); }, $this);
        }

        if (is_null($this->right))
        {
            $this->right = $elt;
        }
        elseif (is_null($this->left))
        {
            $this->left = $elt;
            $this->operator = $operator;
        }
        else
        {
            $filter =  BinaryFilter::create($this)
                ->addFilter($elt, $operator);

            return $filter;
        }

        return $this;
    }

    public function addAnd($filter)
    {
        return $this->addFilter($filter, '&');
    }

    public function addOr($filter)
    {
        return $this->addFilter($filter, '|');
    }

    public function __toString()
    {
        if ($this->isEmpty())
        {
            return '';
        }
        elseif ($this->isPartial())
        {
            return sprintf("(%s)", is_object($this->right) ? $this->right->__toString() :  $this->right);
        }
        else
        {
            return sprintf("(%s%s%s)", $this->operator, 
                is_object($this->left) ? $this->left->__toString() : sprintf("(%s)", $this->left),
                is_object($this->right) ? $this->right->__toString() : sprintf("(%s)", $this->right) 
            );
        }
    }
}
