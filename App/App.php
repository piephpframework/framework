<?php

namespace App;

use Modules\Route\Route;

class App extends Object {

    protected $name, $dependencies;
    protected $route;

    public function __construct($name, array $dependencies){
        $this->name = $name;
        $this->dependencies = $dependencies;
        $this->init();
    }

    public function web(callable $callback){
        call_user_func_array($callback, [($this->route = new Route())]);
    }

    protected function init(){

    }

}