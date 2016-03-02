<?php

namespace Collections\Parsers;

use Collections\Collection;

class Parser extends Collection {

    protected $select = [];
    protected $value = '', $asValue = '';
    protected $order = [], $where = [], $in = [], $union = [];
    protected $limit = null, $offset = 0;

    protected $startTime = 0, $endTime = 0;

    public function setSelect(array $items){
        $this->select = $items;
    }

    public function addWhere(array $item){
        $this->where[] = $item;
    }

    public function addIn(array $item){
        $this->in[] = $item;
    }

    public function addUnion($item){
        $this->union[] = $item;
    }

    public function addOrder(array $item){
        $this->order[] = $item;
    }

    public function setLimit($limit){
        $this->limit = $limit;
    }

    public function setOffset($offset){
        $this->offset = $offset;
    }

    public function getList(){
        return $this->items;
    }

}