<?php

namespace App;



class Controller {

    protected $controller = null;

    public function __construct($controller){
        $this->controller = $controller;
    }

    /**
     * Runs the current controller
     * @return Controller
     */
    public function run(){
        if(is_callable($this->controller)){
            call_user_func_array($this->controller, []);
        }
        return $this;
    }

}