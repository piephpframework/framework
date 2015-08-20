<?php

namespace Object69;

class Scope{

    protected $properties = [];

    public function __set($name, $value){
        $this->properties[$name] = $value;
    }

    public function __get($name){
        if(isset($this->properties[$name])){
            return $this->properties[$name];
        }
        return '';
    }

    public function __call($name, $arguments){
        if(isset($this->properties[$name])){
            $call = $this->properties[$name];
            $call = $call->bindTo($this);
            return call_user_func_array($call, $arguments);
        }
    }

}
