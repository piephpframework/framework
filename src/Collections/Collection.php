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

    /**
     * Gets the length of the array collection
     * @return int The size of the current array
     */
    public function length(){
        if($this->items instanceof ArrayList){
            return $this->items->length;
        }elseif(is_array($this->items)){
            return count($this->items);
        }
        return 0;
    }

    /**
     * Gets an item at a particular offset
     * @param int $offset The offset of the item in the array. Defaults to 0
     * @return mixed Returns the item at that offset
     */
    public function get($offset = 0){
        if(is_array($this->items)){
            return $this->items[$offset];
        }elseif($this->items instanceof ArrayList){
            return $this->items;
        }
        return $this;
    }

    /**
     * Sets the items in the array and overwrites the old values
     * @param mixed $items The items to be set
     * @return Collection Returns the current collection
     */
    public function setItems($items){
        $this->items = $items;
        return $this;
    }

    /**
     * Gets the current object collection key type
     * @return string The type of objects that are accepted as keys
     */
    public function getKeyType(){
        return $this->keyType;
    }

    /**
     * Gets the current object collection value type
     * @return string The type of objects that are accepted as values
     */
    public function getValueType(){
        return $this->valueType;
    }

    /**
     * Loops through all the items in the collection
     * @param callable $callback The callable function to run on the items.<br>
     *      each(function($item){})
     * @return Collection Returns the current collection
     */
    public function each(callable $callback){
        foreach($this->items as $item){
            call_user_func_array($callback, [$item]);
        }
        return $this;
    }

    /**
     * Gets the collection as an array
     * @return array An array of the current collection
     */
    public function toArray(){
        return $this->items;
    }

    /**
     * Resets the internal array pointer to zero
     * @return void
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * Gets the item at the current position
     * @return mixed
     */
    public function current() {
        return $this->items[$this->position];
    }

    /**
     * Gets the current position
     * @return int
     */
    public function key() {
        return $this->position;
    }

    /**
     * Increments the internal array pointer
     * @return void
     */
    public function next() {
        ++$this->position;
    }

    /**
     * Tests if the internal array pointer is valid
     * @return bool
     */
    public function valid() {
        return isset($this->items[$this->position]);
    }

    /**
     * A reference to the internal array
     * @param type $variable Parameter Description
     * @return ref Reference to the array items in the collection
     */
    protected function &arrayRef(){
        return $this->items;
    }
}