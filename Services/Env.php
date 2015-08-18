<?php

namespace Services;

class Env{

    protected $values = [];

    public function set($key, $value){
        $this->values[$key] = $value;
    }

    public function get($key, $default = null){
        if(isset($this->values[$key])){
            return $this->values[$key];
        }
        return $default;
    }

    public function delete($key){
        if(isset($this->values[$key])){
            unset($this->values[$key]);
        }
    }

    public function loadFromFile($filename){
        $this->values = parse_ini_file($filename, true);
    }

}
