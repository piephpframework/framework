<?php

namespace Collections;

class Dictionary extends Collection {

    public function __construct($key, $value){
        if(class_exists($key)){
            $this->keyType = $key;
        }else{
            throw new Exception($key . ' is not a valid class');
        }

        if(class_exists($value)){
            $this->valueType = $value;
        }else{
            throw new Exception($value . ' is not a valid class');
        }
    }

    public function add($key, $value){
        if($key instanceof $this->keyType && $value instanceof $this->valueType){
            if(!$this->keyExists($key, $index)){
                $this->items[] = ['key' => $key, 'value' => $value];
            }else{
                $this->items[$index] = ['key' => $key, 'value' => $value];
            }
        }
        if(!($key instanceof $this->keyType)){
            throw new Exception(get_class($key) . ' is not an instances of ' . $this->keyType);
        }
        if(!($value instanceof $this->valueType)){
            throw new Exception(get_class($value) . ' is not an instances of ' . $this->valueType);
        }
        return $this;
    }

    /**
     * Checks to see if an item is in the dictionary
     * @param class $value
     * @return ArrayList
     */
    public function has($value, &$index = null){
        foreach ($this->items as $idx => $item) {
            if($value === $item['value']){
                $index = $idx;
                return true;
            }
        }
        return false;
    }

    public function keyExists($key, &$index = null){
        foreach ($this->items as $idx => $item) {
            if(get_class($key) === get_class($item['key'])){
                $index = $idx;
                return true;
            }
        }
        return false;
    }

}