<?php

namespace Collections\Processors;

use Exception;
use Database\Model;
use Database\Db;
use Database\ResultSet;
use Database\Row;
use Collections\ArrayList;

class MysqlProcessor extends Processor implements IParser {

    protected $model = null, $resultSets = null;
    protected $query = '';

    protected $finalWhere = '', $finalLimit = '', $finalOrder = '';

    protected $replacements = [];

    public function __construct(Model $model){
        $this->items = new ArrayList(Row::class);
        $this->model = $model;
    }

    /**
     * Processes the Collection search
     * @return void
     */
    public function process(){
        $this->where();
        $this->order();
        $this->limit();
        $this->select();
    }

    /**
     * Builds the mysql where clause
     * @return void
     */
    protected function where(){
        $where = [];
        foreach($this->where as $whereValue){
            $key1 = Db::tick($whereValue['key1']);
            $key2 = $whereValue['key2'];
            $comp = $whereValue['comp'];
            $where[] = $key1 . $comp . '?';
            $this->replacements[] = $key2;
        }
        foreach($this->in as $inValue){
            $key = Db::tick($inValue['key']);
            $vals = $inValue['vals'];
            $where[] = $key . ' in(' . implode(',', array_pad([], count($vals), '?')) . ')';
            foreach($vals as $val){
                $this->replacements[] = $val;
            }
        }
        if(count($where) > 0){
            $this->finalWhere = ' where ' . implode(' and ', $where);
        }
    }

    /**
     * Builds the mysql order clause
     * @return void
     */
    protected function order(){
        $orders = [];
        foreach ($this->order as $orderValue) {
            $orderby = Db::tick($orderValue['orderBy']);
            $direction = $orderValue['direction'];
            if(!in_array($direction, ['asc', 'desc'])){
                throw new Exception('Invalid order direction ' . $orderby);
            }
            $orders[] = $orderby . ' ' . $direction;
        }
        if(count($orders) > 0){
            $this->finalOrder = ' order by ' . implode(',', $orders);
        }
    }


    /**
     * Builds the mysql select clause
     * @return void
     */
    protected function select(){
        $columns = '*';
        if(count($this->select) > 0){
            array_walk($this->select, function(&$value){
                $value = Db::tick($value);
            });
            $columns = implode(',', $this->select);
        }

        $finalSelect = 'select ' . $columns . ' from ' . $this->model->table;
        $query = $finalSelect . ' ' . $this->finalWhere . ' ' . $this->finalOrder . ' ' . $this->finalLimit;

        if(strlen($this->finalWhere) == 0 && strlen($this->finalLimit) == 0){
            throw new Exception('No where or limit found in your query: ' . $query);
        }

        $db = new Db();
        $results = $db->query($query, $this->replacements)->get();
        foreach ($results as $result) {
            $this->items->add(new Row($result));
        }
        $this->addAttributes();
    }


    /**
     * Builds the mysql limit clause
     * @return void
     */
    protected function limit(){
        if($this->limit !== null){
            $this->finalLimit = ' limit ' . (int)$this->offset . ', ' . (int)$this->limit;
        }
    }

    /**
     * Adds the extra attributes that get appended based on the Model
     * @return void
     */
    protected function addAttributes(){
        foreach ($this->items as $row) {
            foreach ($this->model->append as $append) {
                $call = 'append' . str_replace(' ', '', ucwords(str_replace('_', ' ', $append)));
                $result = call_user_func_array([$this->model, $call], [$row]);
                $row->$append = $result;
            }
        }
    }

}