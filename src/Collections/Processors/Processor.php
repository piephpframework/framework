<?php

namespace Collections\Processors;

use Collections\Collection;

class Processor extends Collection {

    protected $select = [];
    protected $value = '', $asValue = '';
    protected $order = [], $where = [], $in = [], $union = [];
    protected $limit = null, $offset = 0;

    protected $startTime = 0, $endTime = 0;

    /**
     * Sets the select attributes
     * @param array $items An array of attributes to select
     * @return void
     */
    public function setSelect(array $items){
        $this->select = $items;
    }

    /**
     * Adds additional where statements
     * @param array $item An array of where commands
     * @return void
     */
    public function addWhere(array $item){
        $this->where[] = $item;
    }

    /**
     * Adds additional in statements
     * @param array $item An array of in commands
     * @return void
     */
    public function addIn(array $item){
        $this->in[] = $item;
    }

    /**
     * Adds additional union statements
     * @param array $item An array of union commands
     * @return void
     */
    public function addUnion($item){
        $this->union[] = $item;
    }

    /**
     * Adds additional order statements
     * @param array $item An array of order commands
     * @return void
     */
    public function addOrder(array $item){
        $this->order[] = $item;
    }

    /**
     * Sets the limit
     * @param int $item The number of items to return
     * @return void
     */
    public function setLimit($limit){
        $this->limit = $limit;
    }

    /**
     * Sets the offset
     * @param int $item The offset to start at
     * @return void
     */
    public function setOffset($offset){
        $this->offset = $offset;
    }

    /**
     * Gets the list of items
     * @return ArrayList The list of items
     */
    public function getList(){
        return $this->items;
    }

}