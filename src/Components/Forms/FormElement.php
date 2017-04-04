<?php

namespace Manix\Brat\Components\Forms;

trait FormElement {

    protected $attributes = [];

    public function setAttribute($attribute, $value) {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    public function getAttribute($attribute) {
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
    }

    public function getAttributes() {
        return $this->attributes;
    }
    
    public function removeAttribute($attribute) {
        unset($this->attributes[$attribute]);
    }

    public function __get($attribute) {
        return $this->getAttribute($attribute);
    }

    public function __set($attribute, $value) {
        return $this->setAttribute($attribute, $value);
    }

}
