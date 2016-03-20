<?php

namespace Application;

use Application\App;

class Pie extends Object {

    private function __construct(){}

    /**
     * Creates a new appliction
     * @param string $name The name of the application
     * @return App
     */
    public static function app($name = '', array $dependencies = []) {

        $app = new App($name, $dependencies);
        return $app;

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
            }elseif($path[$i] == '$route'){
                $obj = RouteParams::$parameters;
            }elseif($path[$i] == '$root'){
                $obj = Pie::$rootScope;
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