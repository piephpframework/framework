<?php

namespace Application;

use Modules\Route\Route;
use Modules\Console\Console;

class App extends Object {

    protected $name, $dependencies;
    protected $route, $console;

    public function __construct($name, array $dependencies){
        $this->name = $name;
        $this->dependencies = $dependencies;
        $this->init();
    }

    public function web(callable $callback){
        call_user_func_array($callback, [($this->route = new Route())]);
    }

    public function console(callback $callback){
        call_user_func_array($callback, [($this->console = new Console())]);
    }

    protected function init(){

    }

}