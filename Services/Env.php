<?php

namespace Object69\Services;

class Env extends Service{

    public function set($key, $value){
        $_ENV[$key] = $value;
    }

    public function get($key, $default = null){
        if(isset($_ENV[$key])){
            return $_ENV[$key];
        }
        return $default;
    }

    public function delete($key){
        if(isset($_ENV[$key])){
            unset($_ENV[$key]);
        }
    }

    public static function loadFromFile($filename){
        $_ENV = parse_ini_file($filename, true);
    }

}
