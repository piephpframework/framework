<?php

namespace Pie\Crust;

class Env extends Service{

    /**
     * Sets an item in the environment
     * @param mixed $key The key of the item
     */
    public function set($key, $value){
        $_ENV[$key] = $value;
    }

    /**
     * Gets an item from the environment
     * @param mixed $key The key of the item
     */
    public function get($key, $default = null){
        if(isset($_ENV[$key])){
            return $_ENV[$key];
        }
        return $default;
    }

    /**
     * Deletes an item from the environment
     * @param mixed $key The key of the item
     */
    public function delete($key){
        if(isset($_ENV[$key])){
            unset($_ENV[$key]);
        }
    }

    /**
     * Finds an item in the environment using dot notation
     * @param string $string The item to find
     * @return mixed The item at the location
     */
    public function find($string){
        return parent::_find($string, $_ENV);
    }

    /**
     * Loads an ini file into the environment<br>
     * This will merge the current evironment with the new one
     * @param string $filename the path the the ini file
     * @param bool $use_include_path Whether or not to use the include path
     */
    public static function loadFromFile($filename, $use_include_path = false){
        $ini = parse_ini_string(file_get_contents($filename, $use_include_path), true);
        $_ENV = array_merge($_ENV, $ini);
    }

    /**
     * Loads an ini file into the environment<br>
     * This will replace the current environment with the new one
     * @param string $filename the path the the ini file
     * @param bool $use_include_path Whether or not to use the include path
     */
    public static function loadNewFile($filename, $use_include_path = false){
        $_ENV = parse_ini_string(file_get_contents($filename, $use_include_path), true);
    }

}
