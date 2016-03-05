<?php

namespace Application;

class ApplicationController {

    protected $controller = null;

    public function __construct($controller = null){
        $this->controller = $controller;
    }

    /**
     * Runs the current controller
     * @return mixed Returns the response from the executed controller
     */
    public function run(){
        $response = null;
        if(is_callable($this->controller)){
            $response = call_user_func_array($this->controller, []);
        }elseif(is_string($this->controller)){
            list($path, $method) = explode(':', $this->controller);
            $class = 'App\\Controllers\\' . $path;
            $init = new $class();
            $response = $init->$method();
        }elseif($this->controller instanceof View){
            $response = $this->controller;
        }
        return $response;
    }

}