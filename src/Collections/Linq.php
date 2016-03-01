<?php

namespace Collections;

use Exception;
use App\Object;

class Linq extends Collection {

    protected $select = [];
    protected $value = '', $asValue = '';
    protected $order = [], $where = [], $in = [], $union = [];
    protected $limit = null, $offset = 0;

    protected $startTime = 0, $endTime = 0;

    private function __construct($items){
        $this->items = $items;
        $this->valueType = $items->getValueType();
        $this->startTime = microtime(true);
    }

    public static function from($items){
        return new Linq($items);
    }

    public function where($val1, $val2, $val3 = ''){
        $this->where[] = [
            'key1' => $val1,
            'comp' => func_num_args() == 2 ? '=' : $val2,
            'key2' => func_num_args() == 2 ? $val2 : $val3
        ];
        return $this;
    }

    public function in($value, array $tests){
        $this->in[] = [
            'key'  => $value,
            'vals' => $tests
        ];
        return $this;
    }

    public function union($arrayListSet){
        $this->union[] = $arrayListSet;
        return $this;
    }

    public function order($attribute, $direction = 'asc'){
        $this->order[] = ['orderBy' => $attribute, 'direction' => $direction];
        return $this;
    }

    public function limit($limit, $offset = 0){
        $this->limit = (int)$limit;
        $this->offset = (int)$offset;
        return $this;
    }

    public function select(){
        $this->select = func_get_args();
        $list = new ArrayList($this->valueType);
        $this->_where($list);
        $this->_order($list);
        $this->_select($list);
        $this->_limit($list);
        $this->items = $list;
        $this->endTime = microtime(true);
        $this->_setMeta();
        return $this;
    }

    private function _setMeta(){
        $meta = new Object();
        $meta->time = $this->endTime - $this->startTime;
        $meta->length = $this->length;

        $this->setMeta($meta);
    }

    private function _where($list){
        if(count($this->where) == 0 && count($this->in) == 0){
            $list->set($this->items->toArray());
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
        $list->set($tmpList);
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

    private function _order($list){
        if(count($this->order) == 0){
            return;
        }
        $tmpList = &$list->arrayRef();
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

    private function _select($list){
        if(!is_array($this->select)){
            return $list;
        }
        $tmpList = &$list->arrayRef();
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
            $list = new ArrayList($this->valueType);
            $list->set($newList);
        }else{
            $list = new ArrayList(Object::class);
            $list->set($newList);
        }
    }

    private function _limit($list){
        if($this->limit === null || $this->limit < $this->offset){
            return;
        }
        $tmpList = &$list->arrayRef();
        $newList = [];
        for($i = $this->offset; $i < $this->limit + $this->offset; $i++){
            if(isset($tmpList[$i])){
                $newList[] = $tmpList[$i];
            }
        }
        $list->set($newList);
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