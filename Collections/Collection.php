<?php

namespace Collections;

use Iterator;
use stdClass;

abstract class Collection implements Iterator {

    protected $items = [], $position = 0;
    protected $keyType = '', $valueType  = '';

    public function getKeyType(){
        return $this->keyType;
    }

    public function getValueType(){
        return $this->valueType;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->items[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->items[$this->position]);
    }

}