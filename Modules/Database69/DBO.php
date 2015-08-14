<?php

namespace Modules\Database69;

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
     * Gets item from database
     */
    public function get(){

    }

    /**
     * Inserts item into database
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
     * Edits an item in the database
     */
    public function edit(){

    }

    /**
     * Removes item from the database
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
        return array_values($this->fields);
    }

    protected function getTable(){
        if($this->db->validName($this->table)){
            return $this->table;
        }
        throw new Exception("Invalid table name '$this->table'");
    }

}
