<?php

namespace Collections\Processors;

use Application\Object;
use Collections\Collection;
use Collections\ArrayList;

class ArrayListProcessor extends Processor implements IParser {

    public function __construct(Collection $items){
        $this->items = $items;
        $this->valueType = $items->getValueType();
        $this->startTime = microtime(true);
    }

    /**
     * Processes the Collection search
     * @return void
     */
    public function process(){
        $this->where();
        $this->order();
        $this->select();
        $this->limit();
        $this->setObjectMeta();
    }

    protected function setObjectMeta(){
        $meta = new Object();
        $meta->time = microtime(true) - $this->startTime;
        $meta->length = $this->length;

        $this->setMeta($meta);
    }

    /**
     * Filters the items in the current list and all union lists based on the where statements to make a new list
     * @return void
     */
    protected function where(){
        if(count($this->where) == 0 && count($this->in) == 0){
            $this->items->set($this->items->toArray());
            return;
        }
        $orderCount = count($this->order);
        $tmpList = [];
        $items = 0;
        // Test current List
        foreach($this->items as $itemValue){
            $inPass = $this->_inTest($itemValue);
            $wherePass = $this->_whereTest($itemValue);
            if($inPass && $wherePass){
                $tmpList[] = $itemValue;
                $items++;
            }
            if($items === $this->limit && $orderCount === 0){
                break;
            }
        }
        // Test Union Lists
        if($items !== $this->limit){
            foreach($this->union as $unionValue){
                foreach($unionValue->toArray() as $itemValue){
                    $inPass = $this->_inTest($itemValue);
                    $wherePass = $this->_whereTest($itemValue);
                    if($inPass && $wherePass){
                        $tmpList[] = $itemValue;
                        $items++;
                    }
                    if($items === $this->limit && $orderCount === 0){
                        break 2;
                    }
                }
            }
        }
        $this->items->set($tmpList);
    }

    /**
     * Tests the where statements
     * @param mixed $itemValue The Object to test
     * @return bool Whether or not the item is insertable
     */
    private function _whereTest($itemValue){
        $insertable = true;
        foreach($this->where as $whereValue){
            $key1 = $whereValue['key1'];
            $key2 = $whereValue['key2'];
            $comp = $whereValue['comp'];
            if(!property_exists($itemValue, $key1)){
                throw new Exception('Where: ' . $key1 . ' does not exist in ' . get_class($itemValue));
            }
            if(!$this->_compare($itemValue->$key1, $key2, $comp)){
                $insertable = false;
                break;
            }
        }
        return $insertable;
    }

    /**
     * Tests if the item is in the array list
     * @param mixed $itemValue The Object to test
     * @return bool Whether or not the item is insertable
     */
    private function _inTest($itemValue){
        $insertable = true;
        foreach($this->in as $inValue){
            $key  = $inValue['key'];
            $vals = $inValue['vals'];
            if(!property_exists($itemValue, $key)){
                throw new Exception('Where: ' . $key . ' does not exist in ' . get_class($itemValue));
            }
            if(!in_array($itemValue->$key, $vals)){
                $insertable = false;
                break;
            }
        }
        return $insertable;
    }

    /**
     * Orders the items in the list
     * @return void
     */
    protected function order(){
        if(count($this->order) == 0){
            return;
        }
        $tmpList = &$this->items->arrayRef();
        foreach($this->order as $order){
            $orderBy = $order['orderBy'];
            $direction = $order['direction'];
            // if(!property_exists($tmpList[0], $orderBy)){
            //     throw new Exception('Order: ' . $orderBy . ' does not exist in ' . get_class($tmpList[0]));
            // }
            usort($tmpList, function($a, $b) use ($orderBy, $direction){
                if($direction == 'desc'){
                    return $b->$orderBy > $a->$orderBy;
                }elseif($direction == 'asc'){
                    return $b->$orderBy < $a->$orderBy;
                }
            });
        }
    }

    /**
     * Selects the keys from each object
     * @return void
     */
    protected function select(){
        if(!is_array($this->select)){
            return $this->items;
        }
        $tmpList = &$this->items->arrayRef();
        $newList = [];
        $selects = count($this->select);

        foreach($tmpList as $value){
            if($selects == 0){
                $newList[] = $value;
            }else{
                $class = new Object;
                foreach($this->select as $selectValue){
                    if(!property_exists($value, $selectValue)){
                        throw new Exception('Select: ' . $selectValue . ' does not exist in ' . get_class($value));
                    }
                    $class->$selectValue = $value->$selectValue;
                }
                $newList[] = $class;
            }
        }
        if($selects == 0){
            $this->items = new ArrayList($this->valueType);
            $this->items->set($newList);
        }else{
            $this->items = new ArrayList(Object::class);
            $this->items->set($newList);
        }
    }

    /**
     * Limits the number of items to return
     * @return void
     */
    protected function limit(){
        if($this->limit === null || $this->limit < $this->offset){
            return;
        }
        $tmpList = &$this->items->arrayRef();
        $newList = [];
        for($i = $this->offset; $i < $this->limit + $this->offset; $i++){
            if(isset($tmpList[$i])){
                $newList[] = $tmpList[$i];
            }
        }
        $this->items->set($newList);
    }

    /**
     * Compares items
     * @param mixed $val1 The first value in the comparison
     * @param mixed $val2 The second value in the comparison
     * @param string $comp The comparison operator
     * @return bool Whether or not the comparison passed or failed
     */
    private function _compare($val1, $val2, $comp){
        switch ($comp) {
            case '=':
                return $val1 == $val2;
            case '>':
                return $val1 > $val2;
            case '<':
                return $val1 < $val2;
            case '>=':
                return $val1 >= $val2;
            case '<=':
                return $val1 <= $val2;
            case '!=':
                return $val1 != $val2;
        }
        return false;
    }
}