<?php

namespace Collections;

use Iterator;
use stdClass;
use Application\Object;

abstract class Collection extends Object implements Iterator {

    protected $items = [], $position = 0;
    protected $keyType = '', $valueType  = '';

    public function __get($name){
        switch ($name) {
            case 'length':
                return $this->length();
        }
    }

    public function length(){
        if($this->items instanceof ArrayList){
            return $this->items->length;
        }elseif(is_array($this->items)){
            return count($this->items);
        }
        return 0;
    }

    public function get($offset = 0){
        if(is_array($this->items)){
            return $this->items[$offset];
        }elseif($this->items instanceof ArrayList){
            return $this->items;
        }
        return $this;
    }

    public function setItems($items){
        $this->items = $items;
        return $this;
    }

    public function getKeyType(){
        return $this->keyType;
    }

    public function getValueType(){
        return $this->valueType;
    }

    public function each(callable $callback){
        foreach($this->items as $item){
            call_user_func_array($callback, [$item]);
        }
        return $this;
    }

    public function toArray(){
        return $this->items;
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

    protected function &arrayRef(){
        return $this->items;
    }
}