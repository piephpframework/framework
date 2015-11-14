<?php

namespace Pie;

// Core
use Pie\Crust\App;
use Pie\Crust\Env;
use Pie\Crust\RootScope;
use Pie\Crust\Net\Request;

// Modules
use Pie\Modules\Route\RouteParams;

class Pie{

    public static $root      = null;
    public static $rootScope = null;

    protected static $firstLoad  = false;

    /**
     *
     * @param string $name
     * @param array $depend
     * @return App
     */
    public static function module($name, array $depend = []){
        $app = new App($name, $depend);
        // Configure the main app
        if(self::$firstLoad !== true){
            $docRoot = Pie::find('$server.DOCUMENT_ROOT');
            // Attempt to load default config
            if(is_file($docRoot . '/../config.ini')){
                Env::loadFromFile($docRoot . '/../config.ini');
            }

            // Create the root scope
            Pie::$rootScope = new RootScope();

            // Initiate common services
            $app->service('request', new Request());

            self::$firstLoad = true;
        }
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

    public static function findRecursive($find, $scope){
        $value   = Pie::find($find, $scope);
        if($value === ''){
            $cscope = $scope->getParentScope();
            do{
                if($cscope === null){
                    break;
                }
                $value = Pie::find($find, $cscope);
                if($value !== null){
                    break;
                }
                $cscope = $cscope->getParentScope();
            }while(true);
        }
        return $value;
    }

}
