<?php

/**
 *
 * @author Ryan Naddy <rnaddy@corp.acesse.com>
 * @name ResultSet.php
 * @version 1.0.0 Sep 30, 2015
 */

namespace Object69\Modules\Database69;

use Iterator;

/**
 * @property int $length The number of items in the result set
 */
class ResultSet implements Iterator{

    protected
        $items  = [],
        $length = 0;

    public function __construct(array $items = []){
        $this->items  = $items;
        $this->length = count($items);
    }

    public function __get($name){
        switch($name){
            case 'length':
                return count($this->items);
            default:
                if(isset($this->items[$name])){
                    return $this->items[$name];
                }elseif(isset($this->$name)){
                    return $this->$name;
                }
                return '';
        }
    }

    public function get($key, $default = null){
        if(isset($this->items[$key])){
            return $this->items[$key];
        }
        return $default;
    }

    /**
     *
     * @param type $value1
     * @param type $value2
     * @return \Object69\Modules\Database69\ResultSet
     */
    public function add($value1, $value2 = null){
        return $this;
    }

    public function applyFilter($fields, callable $callback){
        if(!is_array($fields)){
            $fields = [$fields];
        }
        foreach($this->items as $index => $item){
            if(is_array($item)){
                foreach($item as $key => $value){
                    if(in_array($key, $fields)){
                        $this->items[$index][$key] = call_user_func_array($callback, [$value, $key]);
                    }
                }
            }else{
                if(in_array($key, $fields)){
                    $this->items[$index] = call_user_func_array($callback, [$item, $index]);
                }
            }
        }
        return $this;
    }

    public function toArray(){
        return $this->items;
    }

    /**
     * Gets the current list item
     * @return mixed
     */
    public function current(){
        return current($this->items);
    }

    /**
     * Gets the current list item's key
     * @return mixed
     */
    public function key(){
        return key($this->items);
    }

    /**
     * Gets the next item in the list
     * @return mixed
     */
    public function next(){
        return next($this->items);
    }

    /**
     * Sets the cursor to the beginning of the list
     * @return mixed
     */
    public function rewind(){
        return reset($this->items);
    }

    /**
     * Checks if an item is valid
     * @return boolean
     */
    public function valid(){
        return isset($this->items[$this->key()]);
    }

}
