<?php

namespace Database;

use App\Object;
use Collections\Collection;

class Model extends Collection {

    protected $table = '';

    public function __get($name){
        switch ($name) {
            case 'table':
                return $this->table;
        }
        parent::__get();
    }

    public function __construct(){
        $this->valueType = ResultSet::class;
    }

}