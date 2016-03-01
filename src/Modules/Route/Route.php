<?php

namespace Modules\Route;

use Collections\ArrayList;

class Route {

    protected $paths;
    protected $prefix = '';

    public function __construct(){
        $this->paths = new ArrayList(Path::class);
    }

    public function getPaths(){
        return $this->paths;
    }

    public function group(array $options, callable $callback){
        $this->prefix($options);
        call_user_func_array($callback, [$this]);
        $this->dePrefix();
    }

    public function when($path, $controller, $requestType = RequestType::All){
        $routePath = preg_replace('/\/\/+/', '/', '/' . $this->prefix . '/' . $path);
        $path = new Path($routePath, $controller, $requestType);
        $this->paths->add($path);
        return $this;
    }

    public function get($path, $controller){
        $this->when($path, $controller, RequestType::Get);
    }

    public function post($path, $controller){
        $this->when($path, $controller, RequestType::Post);
    }

    public function put($path, $controller){
        $this->when($path, $controller, RequestType::Put);
    }

    public function patch($path, $controller){
        $this->when($path, $controller, RequestType::Patch);
    }

    public function delete($path, $controller){
        $this->when($path, $controller, RequestType::Delete);
    }

    protected function prefix($options){
        if(isset($options['prefix'])){
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }
    }

    protected function dePrefix(){
        $path = explode('/', $this->prefix);
        array_pop($path);
        $this->prefix = '/' . implode($path);
        return $this;
    }

}