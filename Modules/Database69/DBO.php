<?php

namespace Object69\Modules\Database69;

use Exception;

/**
 * @property Db $db Database Connection
 */
class DBO{

    protected $table  = null;
    protected $db;
    protected $fields = [];

    public function __construct($table, Db $db){
        $this->table = $table;
        $this->db    = $db;
    }

    public function __set($name, $value){
        $this->fields[$name] = $value;
    }

    public function __get($name){
        return $this->fields[$name];
    }

    /**
     * Gets items from the database
     * @return stdClass
     */
    public function get(array $settings = []){
        $values = $this->getValues();
        $table  = $this->getTable();
        $where  = $this->getWhere($settings);
        $order  = $this->getOrder($settings);
        $limit  = $this->getLimit($settings);

        $query = "select * from $table $where $order $limit";

        $this->reset();
        if(isset($settings['limit']) && $settings['limit'] == 1){
            return $this->db->getRow($query, $values);
        }
        return $this->db->getAll($query, $values);
    }

    /**
     * Inserts items into the database
     * @return int
     */
    public function save(){
        if(empty($this->fields)){
            return false;
        }
        $columns     = $this->getColumns();
        $values      = $this->getValues();
        $placeholers = $this->getPlaceholders();
        $table       = $this->getTable();
        $query       = "insert into $table ($columns) values ($placeholers)";

        $this->db->query($query, $values);
        $this->reset();
        return $this->db->rowCount();
    }

    /**
     * Edits items in the database
     */
    public function edit(){

    }

    /**
     * Removes items from the database
     */
    public function remove(){

    }

    public function reset(){
        $this->fields = [];
    }

    protected function getPlaceholders(){
        $vals = array_values($this->fields);
        return implode(",", array_pad([], count($vals), "?"));
    }

    protected function getColumns(){
        $keys = array_keys($this->fields);
        return implode(",", $keys);
    }

    protected function getValues(){
        $vals = array_values($this->fields);
        $arr  = [];
        foreach($vals as $val){
            if(is_array($val)){
                $arr = array_merge($arr, $val);
            }else{
                $arr[] = $val;
            }
        }
        return array_map(function($item){
            return ltrim($item, '!><');
        }, $arr);
    }

    protected function getTable(){
        if($this->db->validName($this->table)){
            return $this->table;
        }
        throw new Exception("Invalid table name '$this->table'");
    }

    protected function getWhere(array $settings = []){
        $keys = array_keys($this->fields);
        $vals = array_values($this->fields);

        $items = [];
        foreach($keys as $index => $value){
            if($vals[$index][0] == '!'){
                $items[] = "$value != ?";
            }elseif($vals[$index][0] == '>'){
                $items[] = "$value > ?";
            }elseif($vals[$index][0] == '<'){
                $items[] = "$value < ?";
            }elseif(is_array($vals[$index])){
                $items[] = "$value in(" . implode(',', array_pad([], count($vals[$index]), '?')) . ")";
            }else{
                $items[] = "$value = ?";
            }

            if(isset($settings['comp']) && count($settings['comp']) > 0){
                if(isset($settings['comp'][$index])){
                    $items[] = $settings['comp'][$index];
                }
            }
        }
        $str = count($items) > 0 ? ' where ' : '';
        if(isset($settings['comp']) && count($settings['comp']) > 0){
            return $str . implode(' ', $items);
        }
        return $str . implode(' and ', $items);
    }

    protected function getLimit(array $settings){
        if(isset($settings['limit'])){
            return 'limit ' . (int)$settings['limit'];
        }
        return '';
    }

    protected function getOrder(array $settings){
        if(isset($settings['order'])){
            $dirs = [];
            foreach($settings['order'] as $column => $dirc){
                $col    = is_int($column) ? $dirc : $column;
                $dir    = is_int($column) ? 'asc' : $dirc;
                $dirs[] = $col . ' ' . $dir;
            }
            return 'order by ' . implode(', ', $dirs);
        }
        return '';
    }

}
