<?php

namespace Collections\Parsers;

use Exception;
use Database\Model;
use Database\Db;
use Database\ResultSet;
use Collections\ArrayList;

class MysqlParser extends Parser implements IParser {

    protected $model = null;
    protected $query = '';

    protected $finalWhere = '', $finalLimit = '', $finalOrder = '';

    protected $replacements = [];

    public function __construct(Model $model){
        $this->items = new ArrayList(ResultSet::class);
        $this->model = $model;
    }

    public function process(){
        $this->where();
        $this->order();
        $this->limit();
        $this->select();
        $this->setObjectMeta();
    }

    protected function where(){
        $where = [];
        foreach($this->where as $whereValue){
            $key1 = '`' . implode('`.`', explode('.', $whereValue['key1'])) . '`';
            $key2 = $whereValue['key2'];
            $comp = $whereValue['comp'];
            $where[] = $key1 . $comp . '?';
            $this->replacements[] = $key2;
        }
        foreach($this->in as $inValue){
            $key = '`' . implode('`.`', explode('.', $inValue['key'])) . '`';
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

    protected function order(){}

    protected function select(){
        $columns = '*';
        if(count($this->select) > 0){
            $columns = implode($this->select);
        }
        $finalSelect = 'select ' . $columns . ' from ' . $this->model->table;

        $query = $finalSelect . ' ' . $this->finalWhere . ' ' . $this->finalLimit;

        if(strlen($this->finalWhere) == 0 && strlen($this->finalLimit) == 0){
            throw new Exception('No where or limit found in your query: ' . $query);
        }

        $db = new Db();
        $results = $db->query($query, $this->replacements)->get();
        foreach ($results as $result) {
            $this->items->add(new ResultSet($result));
        }
    }

    protected function limit(){
        if($this->limit !== null){
            $this->finalLimit = ' limit ' . (int)$this->offset . ', ' . (int)$this->limit;
        }
    }

    protected function setObjectMeta(){}

}