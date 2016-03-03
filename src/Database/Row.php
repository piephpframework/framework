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

    public function __set($key, $value){
        return $this->row->$key = $value;
    }

    public function __construct($item = null){
        $this->set($item);
    }

    public function set($item){
        $this->row = $item;
    }

}