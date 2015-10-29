<?php

namespace Object69\Modules\Route69;

use Exception;
use Object69\Core\App;
use Object69\Core\Controller;
use SimpleXMLElement;

class Route{

    protected $routes = array();
    protected $always = array();
    protected $strict = true;

    /**
     * Always set the following settings for each call
     * @param array $settings
     * @return Route
     */
    public function always(array $settings = null){
        $this->always = $settings;
        return $this;
    }

    /**
     * Sets a new route to be tested
     * @param string $path The path of the route
     * @param array $settings The settings fo the route
     * @return Route
     */
    public function when($path, $settings){
        if(!is_array($settings) && !($settings instanceof Controller)){
            throw new Exception('$settings must be either an array or instance of Controller');
        }
        $this->routes[] = array(
            "path"     => $path,
            "settings" => $settings
        );
        return $this;
    }

    /**
     * If no when statement gets executed default to this
     * @param array $settings
     * @return Route
     */
    public function otherwise(array $settings){
        $this->routes[] = array(
            "fallback" => true,
            "settings" => $settings
        );
        return $this;
    }

    /**
     * Gets a list of all the setup routes
     * @return type
     */
    public function getRoutes(){
        return $this->routes;
    }

    /**
     * Gets a list of the always settings
     * @return type
     */
    public function getAlways(){
        return $this->always;
    }

    /**
     * Turns on/off strict mode
     * @param type $isStrict
     */
    public function setStrict($isStrict){
        $this->strict = (bool)$isStrict;
    }

    /**
     * Gets the current strictness
     * @return type
     */
    public function getStrict(){
        return $this->strict;
    }

    public function findRoute(App $app){
        $services    = $app->getServices();
        $route       = $services['route'];
        $routeParams = $services['routeParams'];
        $path        = $app->path;
        $routes      = $route->getRoutes();
        // Foreach user defined route
        foreach($routes as $r){
            $controller = null;
            $settings   = null;
            // Route::when
            if(isset($r['path'])){
                $route      = $this->pathToArray($r['path']);
                $route_good = false;
                // If the path lengths match, test them
                // Otherwise it isn't worth testing
                if(count($path) == count($route)){
                    foreach($route as $index => $item){
                        if(!isset($path[$index])){
                            $route_good = false;
                            break;
                        }
                        $good = $this->_comparePathItems($path[$index], $route[$index], $app);
                        if(!$good){
                            $route_good = false;
                            break;
                        }
                        if($r['settings'] instanceof Controller){
                            $controller = $r['settings'];
                        }
//                        elseif(isset($r['settings']['controller'])){
//                            $controller = $r['settings']['controller'];
//                        }
                        $settings = $r['settings'];

                        if($good){
                            $route_good = true;
                        }
                    }
                    if(!$route_good){
                        $routeParams = [];
                    }
                }else{
                    $controller = null;
                    $settings   = null;
                }
            }
            if($route_good){
                return [
                    'controller'     => $controller,
                    'settings'       => $settings,
                    'globalSettings' => $this->getAlways()
                ];
            }
        }
        // Our route was not found, use our fallback
        // Route::otherwise
        foreach($routes as $route){
            if(isset($route['fallback'])){

            }
        }
        return null;
    }

    /**
     * Compares the URL path item to the user defined path item
     * @param string $item1 The URL path item
     * @param string $item2 The User defined path item
     * @return boolean
     */
    function _comparePathItems($item1, $item2, App $app){
        $matches = array();

        // Test if item is a parameter
        if(preg_match('/^(:|@|#).+?/', $item2, $matches) && !empty($item1)){
            if($matches[1] == '@' && !ctype_alpha($item1)){
                return false;
            }
            if($matches[1] == '#' && !ctype_digit($item1)){
                return false;
            }
            $val     = ltrim($item2, ':@#');
            $classes = $app->getServices();

            $classes['routeParams']->$val = $item1;
            return true;
        }

        // Test if the two items match
        if($app->getServices()['route']->getStrict()){
            return $item1 === $item2;
        }else{
            return strtolower($item1) == strtolower($item2);
        }
        return false;
    }

    public function executeController(App $parent, Controller $controller){
        $result = $parent->execController($controller);

        $displayAs = null;
        if(isset($controller->settings['displayAs'])){
            $displayAs = isset($controller->settings['displayAs']);
        }elseif(isset($this->getAlways()['displayAs'])){
            $displayAs = $this->getAlways()['displayAs'];
        }

        if($displayAs !== null){
            switch($displayAs){
                case 'json':
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    break;
                case 'xml':
                    $xml = new SimpleXMLElement('<root/>');
                    array_walk_recursive($result, array($xml, 'addChild'));
                    header('Content-Type: application/xml');
                    echo $xml->asXML();
                    break;
                default:
                    echo $result;
                    break;
            }
        }
    }

    /**
     * Converts a strng path to an array removing the prefixed '/'
     * @param string $path
     * @return string
     */
    public function pathToArray($path){
        $items = explode('/', ltrim($path, '/'));
        return array_map(function($value){
            return explode('?', $value, 2)[0];
        }, $items);
    }

    public function queryString($path){
        $pu = parse_url($path);
        return isset($pu['query']) ? $pu['query'] : '';
    }

}
