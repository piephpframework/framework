<?php

namespace Collections\Parsers;

use App\Object;
use Collections\Collection;
use Collections\ArrayList;

class ArrayListParser extends Parser implements IParser {

    public function __construct(Collection $items){
        $this->items = $items;
        $this->valueType = $items->getValueType();
        $this->startTime = microtime(true);
    }

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