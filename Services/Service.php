<?php

namespace Object69\Services;

use Object69\Object69;

class Service{

    protected $values = [];

    public function find($path){
        if($this instanceof Env){
            return Object69::find($_ENV, $path);
        }elseif($this instanceof Session){
            return Object69::find($_SESSION, $path);
        }else{
            return Object69::find($this->values, $path);
        }
    }

}
