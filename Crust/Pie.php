<?php

namespace Object69\Core;

class Object69{

    public static $root      = null;
    public static $rootScope = null;

    /**
     *
     * @param string $name
     * @param array $depend
     * @return App
     */
    public static function module($name, array $depend = []){
        return new App($name, $depend);
    }

    public static function find($path, $obj = null){
        $previous = null;
        for($i = 0, $path = preg_split('/[\[\]\.]/', $path), $len = count($path); $i < $len; $i++){
            if($path[$i] == ''){
                continue;
            }
            if($path[$i] == '$server'){
                $obj = $_SERVER;
            }elseif($path[$i] == '$session'){
                $obj = $_SESSION;
            }elseif($path[$i] == '$env'){
                $obj = $_ENV;
            }elseif($path[$i] == '$get'){
                $obj = $_GET;
            }elseif($path[$i] == '$post'){
                $obj = $_POST;
            }elseif($path[$i] == '$request'){
                $obj = $_REQUEST;
            }elseif($path[$i] == '$cookie'){
                $obj = $_COOKIE;
            }elseif($path[$i] == '$root'){
                $obj = Object69::$rootScope;
            }elseif($path[$i] == '$length'){
                return is_array($previous) ? count($previous) : 0;
            }else{
                $item = ctype_digit($path[$i]) ? (int)$path[$i] : $path[$i];
                if(is_object($obj)){
                    $obj = $obj->{$item};
                }else{
                    $obj = isset($obj[$item]) ? $obj[$item] : '';
                }
            }
            $previous = $obj;
        }
        return $obj;
    }

}

Object69::$rootScope = new RootScope();
