<?php

/**
 *
 * @author Ryan Naddy <rnaddy@corp.acesse.com>
 * @name Controller.php
 * @version 1.0.0 Sep 28, 2015
 */

namespace Object69\Core;

/**
 * @property callable $controller A controller class name or calable
 * @property callable $method A controller method name
 * @property string $name The name of the controller
 * @property array $scope The accessable data within the controller
 * @property Call $call The Call information when the controller ran
 * @property array $settings Settings created usually from the routing information
 */
class Controller{

    protected
            $name       = null,
            $controller = null,
            $method     = null,
            $scope      = null,
            $call       = null,
            $settings   = [];

    public function __construct($name, $callback = null, $method = null){

        $this->name = $name;

        if(is_callable($callback)){
            $this->controller = $callback;
        }elseif(is_string($callback)){
            $this->controller = new $callback();
            $method           = $method !== null ? $method : $callback;
            $this->method     = $method;
        }else{
            throw new Exception('Invalid callback, must be a callable or a string');
        }
        $this->scope = new Scope();
    }

    public function __get($name){
        return $this->$name;
    }

    public function setCall(Call $call){
        $this->call = $call;
    }

    public function setSettings(array $settings){
        $this->settings = $settings;
    }

    public function setScope(Scope $scope){
        $this->scope = $scope;
    }

    public function setController($controller){
        $this->controller = $controller;
    }

    public function setMethod($method){
        $this->method = $method;
    }

    public function getScope(){
        return $this->scope;
    }

    public function run(){
        if($this->method !== null){
            call_user_func_array([$this->controller, $this->method], $param_arr);
        }
    }

}
