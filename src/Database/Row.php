<?php

namespace Database;

class Row {

    protected $row = null;

    public function __get($name){
        if(isset($this->row->$name)){
            return $this->row->$name;
        }
        return null;
    }

    /**
     * The key value for this row to be assigned
     * @param string $key The key for the row
     * @return void
     */
    public function __set($key, $value){
        $this->row->$key = $value;
    }

    /**
     * Creates a new row with optional data
     * @param stdClass $item The row data
     * @return void
     */
    public function __construct($item = null){
        $this->set($item);
    }

    /**
     * Sets the data for the row
     * @param stdClass $item The row data
     * @return void
     */
    public function set($item){
        $this->row = $item;
    }

}