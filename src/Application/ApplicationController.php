<?php

namespace Application;

use Application\View;
use Application\Templates\Tpl;

class ApplicationController {

    protected $controller = null;
    protected $shell;

    public function __construct($controller = null, View $shell = null){
        $this->controller = $controller;
        $this->shell = $shell;
    }

    /**
     * Runs the current controller
     * @return mixed Returns the response from the executed controller
     */
    public function run(array $args = []){
        $response = null;
        if(is_callable($this->controller)){
            $response = call_user_func_array($this->controller, $args);
        }elseif(is_string($this->controller)){
            list($path, $method) = explode(':', $this->controller);
            $class = 'App\\Controllers\\' . $path;
            $init = new $class();
            $response = call_user_func_array([$init, $method], $args);
        }elseif($this->controller instanceof View){
            $response = $this->controller;
        }

        if($this->shell instanceof View && $response instanceof View){
            $tpl = new Tpl();
            return $tpl->getView($response, $this->shell);
        }else{
            return $response;
        }
    }

}