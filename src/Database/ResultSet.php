<?php

namespace Database;

use Application\Object;
use Collections\Collection;
use Collections\ArrayList;
use Database\Row;

class ResultSet extends Collection {

    public function __construct(){
        $this->items = new ArrayList(Row::class);
    }

    public function add(Row $row){
        $this->items->add($row);
    }

    public function __get($name){
        if(is_array($this->items) && isset($this->items[$name])){
            return $this->items[$name];
        }elseif(is_object($this->items) && isset($this->items->$name)){
            return $this->items->$name;
        }
        return null;
    }

    public function __set($key, $value){
        $this->items->$key = $value;
    }

}