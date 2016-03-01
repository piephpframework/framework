<?php

namespace Pie\Modules\Route;

/**
 *
 * @author Ryan Naddy <untuned20@gmail.com>
 * @name RouteParams.php
 * @version 1.0.0 Aug 3, 2015
 */
class RouteParams{

    public static $parameters = [];
    private $length = 0;

    public function __get($name){
        if(isset(self::$parameters[$name])){
            return self::$parameters[$name];
        }elseif($name == 'length'){
            return count(self::$parameters);
        }
        return '';
    }

    public function __set($name, $value){
        self::$parameters[$name] = $value;
    }

    /**
     * Gets a parameters value
     * @param string $name
     * @return string
     */
    public function getParameter($name, $default = null){
        if(isset(self::$parameters[$name])){
            return self::$parameters[$name];
        }
        return $default;
    }

    /**
     * Gets a list of all the parameters
     * @return array
     */
    public function getParameters(){
        return self::$parameters;
    }

}
