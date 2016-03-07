<?php

namespace Application\Database;

use Application\Object;
use Collections\Collection;

class Model extends Collection {

    protected $table = '';
    protected $append = [];

    public function __get($name){
        switch ($name) {
            case 'table':   return $this->table;
            case 'append':  return $this->append;
            case 'attributes':  return $this->items;
        }
        parent::__get($name);
    }

    public function __call($name, $args){
        if(method_exists($this, $name)){
            return call_user_func_array([$this, $name], $args);
        }
    }

    public function __construct(){
        $this->valueType = ResultSet::class;
    }

}