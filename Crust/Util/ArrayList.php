<?php

namespace Pie\Crust\Util;

use Exception;
use Iterator;
use Pie\Crust\Service;

/**
 * @property int $count Count of items in list
 */
class ArrayList extends Service implements Iterator{

    protected $items = [];
    protected $type  = '';

    /**
     * Sets the allowed object type
     * @param mixed $type
     * @throws Exception
     */
    public function __construct($type = null){
        if(is_array($type)){
            $this->type = null;
            $this->items = $type;
        }elseif(class_exists($type)){
            $this->type = $type;
        }else{
            throw new Exception($type . ' is not a valid class');
        }
    }

    public function __get($name){
        switch($name){
            case 'count':
                return count($this->items);
        }
    }

    /**
     * Adds an object instance of the defined type
     * @param mixed $object
     * @throws Exception
     */
    public function add($object){
        if($object instanceof $this->type){
            $this->items[] = $object;
        }elseif($this->type === null && gettype($object) == 'array'){
            $this->items[] = $object;
        }else{
            throw new Exception(get_class($object) . ' is not an instances of ' . $this->type);
        }
        return $this;
    }

    /**
     * Resets the items in the list
     * @param array $items
     * @return ArrayList
     */
    public function set(array $items){
        $this->items = [];
        foreach($items as $item){
            $this->add($item);
        }
        return $this;
    }

    /**
     * Inserts an object instances of the defined type at a particular index
     * @param int $index
     * @param mixed $object
     * @throws Exception
     */
    public function insert($index, $object){
        if($object instanceof $this->type){
            $this->items[$index] = $object;
        }else{
            throw new Exception(get_class($object) . ' is not an instances of ' . $this->type);
        }
        return $this;
    }

    /**
     * Empties the list
     */
    public function clear(){
        $this->items = [];
        return $this;
    }

    /**
     * Checks to see if the list contains the object
     * @param mixed $object
     * @return boolean
     */
    public function contains($object){
        foreach($this->items as $item){
            if($item === $object){
                return true;
            }
        }
        return false;
    }

    /**
     * Checks to see if the current item equals the object
     * @param mixed $object
     * @return boolean
     */
    public function equals($object){
        if($object === $this->current()){
            return true;
        }
        return false;
    }

    /**
     * Returns the name of the class of the current object in the list
     * @return string
     */
    public function getType(){
        return get_class($this->current());
    }

    /**
     * Removes an object at a particular index
     * @param int $index
     * @return boolean
     */
    public function removeAt($index){
        if(isset($this->items[$index])){
            unset($this->items[$index]);
            return true;
        }
        return false;
    }

    /**
     * Searches the list for matching items
     * @param mixed $expression1,...
     * @return ArrayList
     */
    public function find($expression1, $expression2 = null){
        $ors     = func_get_args();
        $objects = $this->items;
        foreach($this->items as $index => $item){
            $remove = true;
            $c      = 0;
            foreach($ors as $val){
                foreach($val as $path => $value){
                    if($value != parent::_find($path, $item)){
                        break;
                    }elseif($c == count($ors) - 1){
                        $remove = false;
                        break 2;
                    }
                    $c++;
                }
            }
            if($remove){
                unset($objects[$index]);
            }
        }
        return (new ArrayList($this->type))->set($objects);
    }

    public function each(callable $callback){
        foreach($this->items as $item){
            call_user_func_array($callback, [$item]);
        }
    }

    /**
     * Gets the list as an array
     * @return array
     */
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
