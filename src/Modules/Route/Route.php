<?php

namespace Modules\Route;

use Collections\ArrayList;
use Application\View;

class Route {

    protected $paths;
    protected $prefix = '';
    protected $routeRan = false;

    public function __construct(){
        $this->paths = new ArrayList(Path::class);
    }

    /**
     * Runs the route when the web request dies
     */
    public function __destruct(){
        $foundRoute = $this->findRoute();
        if($foundRoute !== null){
            $result = $this->runRoute($foundRoute);
            $this->handleResult($result);
        }
    }

    public function handleResult($response){
        if($response instanceof View){
            $result = $response->getView();
            if(is_string($result)){
                echo $result;
            }
        }
    }

    /**
     * Gets a list of the paths
     * @return ArrayList
     */
    public function getPaths(){
        return $this->paths;
    }

    /**
     * Runs a path by name
     * @param string The named path
     * @return Route
     */
    public function run($string){
        foreach ($this->paths as $path) {
            if($path->name == $string){
                $result = $this->runRoute($path);
                $this->handleResult($result);
            }
        }
        return $this;
    }

    /**
     * Creates a group of routes
     * @param array $options An array of group options
     * @param callable $callback A callback that contains related routes for the group
     * @return Route
     */
    public function group(array $options, callable $callback){
        $this->prefix($options);
        call_user_func_array($callback, [$this]);
        $this->dePrefix();
        return $this;
    }

    /**
     * Handles all or a specific request type (GET, POST, PUT, PATCH, DELETE)
     * @param string $path The path to be handled
     * @param mixed $controller The controller for this path
     * @param string $requestType The request type for this path
     * @return Path
     */
    public function when($path, $controller, $requestType = RequestType::All){
        $routePath = preg_replace('/\/\/+/', '/', '/' . $this->prefix . '/' . $path);
        $path = new Path($routePath, $controller, $requestType);
        $this->paths->add($path);
        return $path;
    }

    /**
     * Handles GET request types
     * @param string $path The path to be handled
     * @param mixed $controller The controller for this path
     * @return Path
     */
    public function get($path, $controller){
        return $this->when($path, $controller, RequestType::Get);
    }

    /**
     * Handles POST request types
     * @param string $path The path to be handled
     * @param mixed $controller The controller for this path
     * @return Path
     */
    public function post($path, $controller){
        return $this->when($path, $controller, RequestType::Post);
    }

    /**
     * Handles PUT request types
     * @param string $path The path to be handled
     * @param mixed $controller The controller for this path
     * @return Path
     */
    public function put($path, $controller){
        return $this->when($path, $controller, RequestType::Put);
    }

    /**
     * Handles PATCH request types
     * @param string $path The path to be handled
     * @param mixed $controller The controller for this path
     * @return Path
     */
    public function patch($path, $controller){
        return $this->when($path, $controller, RequestType::Patch);
    }

    /**
     * Handles DELETE request types
     * @param string $path The path to be handled
     * @param mixed $controller The controller for this path
     * @return Path
     */
    public function delete($path, $controller){
        return $this->when($path, $controller, RequestType::Delete);
    }

    /**
     * Adds a prefix to the current prefix for the next routes
     * @return Route
     */
    protected function prefix($options){
        if(isset($options['prefix'])){
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }
        return $this;
    }

    /**
     * Removes the last item in the prefix
     * @return Route
     */
    protected function dePrefix(){
        $path = explode('/', $this->prefix);
        array_pop($path);
        $this->prefix = '/' . implode($path);
        return $this;
    }

    /**
     * Runs a path if a path has not yet been run
     * @param Path $path The path to run
     * @return Route
     */
    protected function runRoute(Path $path){
        if(!$this->routeRan){
            $this->routeRan = true;
            return $path->runController();
        }
        return null;
    }

    /**
     * Finds a route to run based on the current REQUEST_URI
     * @return null|Path
     */
    protected function findRoute(){
        if(!isset($_SERVER['REQUEST_URI'])){
            return null;
        }
        $route = array_unique(explode('/', $_SERVER['REQUEST_URI']));
        $method = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));
        $foundPath = null;
        foreach ($this->paths as $tpath) {
            $testPath = array_unique(explode('/', $tpath->path));
            $validPath = false;
            foreach ($testPath as $key => $testDir) {
                if(!isset($route[$key]) || (count($testPath) != count($route))){
                    $validPath = false;
                    break;
                }
                $routeDir = $route[$key];
                if($routeDir == $testDir && ($method == $tpath->method || $tpath->method == RequestType::All)){
                    $validPath = true;
                    continue;
                }
            }
            if($validPath){
                return $tpath;
            }
        }
        return null;
    }

}