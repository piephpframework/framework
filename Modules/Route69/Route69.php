<?php

namespace Modules\Route69;

use App;
use Event;
use Modules\Module;
use Object69;
use SimpleXMLElement;

class Route69 extends Module{

    public function init(App $parent){
        $this->app = Object69::module('Route69', []);

        $this->app->classes = [
            'route'       => new Route(),
            'routeParams' => new RouteParams()
        ];

        $this->app->method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
        $this->app->path   = $this->_pathToArray(filter_input(INPUT_SERVER, 'REQUEST_URI'));

        $this->app->cleanup = function() use ($parent){
            $controller = $this->_findRoute();
            if($controller !== null){
                $parent->exec($controller);
            }
            $event        = new Event();
            $event->name  = 'routeChange';
            $event->value = [$controller, $this->app->classes['route']->getAlways()];
            return $event;
        };

        $this->app->routeChange = function($value) use ($parent){
            if(isset($value[0]['settings']['displayAs'])){
                $this->_executeController($parent, $value[0]);
            }
        };

        return $this->app;
    }

    /**
     * Tests the routes against the current path
     * @return callable The controller to execute
     */
    protected function _findRoute(){
        $routes = $this->app->classes['route']->getRoutes();
        // Foreach user defined route
        foreach($routes as $r){
            $controller = null;
            $settings   = null;
            // Route::when
            if(isset($r['path'])){
                $route      = $this->_pathToArray($r['path']);
                $route_good = false;
                // If the path lengths match, test them
                // Otherwise it isn't worth testing
                if(count($this->app->path) == count($route)){
                    foreach($route as $index => $item){
                        if(!isset($this->app->path[$index])){
                            $route_good = false;
                            break;
                        }
                        $good = $this->_comparePathItems($this->app->path[$index], $route[$index]);
                        if(!$good){
                            $route_good = false;
                            break;
                        }
                        if(isset($r['settings']['controller'])){
                            $controller = $r['settings']['controller'];
                        }
                        $settings = $r['settings'];

                        if($good){
                            $route_good = true;
                        }
                    }
                    if(!$route_good){
                        $this->app->classes['routeParams'] = [];
                    }
                }else{
                    $controller = null;
                    $settings   = null;
                }
            }
            if($route_good){
                return [
                    'controller' => $controller,
                    'settings'   => $settings
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
    protected function _comparePathItems($item1, $item2){
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
            $classes = $this->app->classes;

            $classes['routeParams']->$val = $item1;
            return true;
        }

        // Test if the two items match
        if($this->app->classes['route']->getStrict()){
            return $item1 === $item2;
        }else{
            return strtolower($item1) == strtolower($item2);
        }
        return false;
    }

    protected function _executeController(App $parent, array $controller){
        $result = $parent->execController($controller['controller']);
        if(isset($controller['settings']['displayAs'])){
            switch(strtolower($controller['settings']['displayAs'])){
                case 'json':
                    echo json_encode($result);
                    break;
                case 'xml':
                    $xml = new SimpleXMLElement('<root/>');
                    array_walk_recursive($result, array($xml, 'addChild'));
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
    protected function _pathToArray($path){
        return explode('/', ltrim($path, '/'));
    }

}
