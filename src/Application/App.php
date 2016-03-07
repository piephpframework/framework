<?php

namespace Application;

use Application\Routes\Route;
use Application\Console\Console;
use Application\Server\Server;

class App extends Object {

    protected $name, $dependencies;
    protected $route, $console, $server;

    public function __construct($name, array $dependencies){
        $this->name = $name;
        $this->dependencies = $dependencies;
        // $this->init();
    }

    /**
     * Creates a web handler to handle web requests
     * @param callable $callback A callable function to initiate routes
     * @return App Returns the current application
     */
    public function web(callable $callback){
        call_user_func_array($callback, [($this->route = new Route())]);
        return $this;
    }

    /**
     * Creates a console handler to handle console scripts
     * @param callable $callback A callable function to initiate colsole actions
     * @return App Returns the current application
     */
    public function console(callable $callback){
        call_user_func_array($callback, [($this->console = new Console())]);
        return $this;
    }

    /**
     * Creates a server handler to handle server requests
     * @param callablble $callback A callable function to handle server requests
     * @return App Returns the current application
     */
    public function server(callable $callback){
        call_user_func_array($callback, [($this->server = new Server())]);
        return $this;
    }

}