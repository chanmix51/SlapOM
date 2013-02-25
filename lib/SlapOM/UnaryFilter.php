<?php
namespace SlapOM;

class UnaryFilter
{
    public $element;
    public $operator;

    static public function create($elt, $operator)
    {
        return new self($elt, $operator);
    }

    public function __construct($elt, $operator)
    {
        $this->element = $elt;
        $this->operator = $operator;
    }

    public function __toString()
    {
        return sprintf("(%s%s)", $this->operator, is_object($this->element) ? (string) $this->element : sprintf("(%s)", $this->element));
    }
}
