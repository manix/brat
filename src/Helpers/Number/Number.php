<?php

namespace Manix\Brat\Helpers\Number;

abstract class Number {

    const OP_ADD = '+';
    const OP_SUB = '-';
    const OP_MUL = '*';
    const OP_DIV = '/';
    const OP_MOD = '%';

    /**
     * @var mixed The primitive value of the object.
     */
    protected $value;
    public $round = 2;

    public function __construct($number) {
        $this->value = (float)$number;
    }

    protected function getPrimitive($number) {
        return $number instanceof self ? $number->value : $number;
    }

    public function math($operation, $number) {
        return new static(eval($this->value . $operation . $this->getPrimitive($number)));
    }

    abstract public function __toString();
    
    public function getValue() {
        return $this->value;
    }

}
