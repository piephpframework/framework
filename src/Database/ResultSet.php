<?php

namespace Database;

use App\Object;

class ResultSet {

    protected $item = null;

    public function __construct($item){
        $this->item = $item;
    }

    public function __get($name){
        if(is_array($this->item) && isset($this->item[$name])){
            return $this->item[$name];
        }elseif(is_object($this->item) && isset($this->item->$name)){
            return $this->item->$name;
        }
        return null;
    }

}