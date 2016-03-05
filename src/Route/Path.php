<?php

namespace Route;

use Application\ApplicationController;

class Path {

    protected $path = '', $controller;
    protected $requestType = RequestType::All;
    protected $name = null;


    public function __get($name){
        switch($name){
            case 'path': return $this->path;
            case 'name': return $this->name;
            case 'method': return $this->requestType;
            case 'controller': return $this->controller;
        }
    }

    /**
     * Initializes a new path
     * @param string $path The path for the current path
     * @param mixed $controller The controller for this path
     * @param mixed $requestType The request type that this route accepts
     * @return Path
     */
    public function __construct($path, $controller, $requestType){
        $this->path = $path;
        $this->controller = new ApplicationController($controller);
        $this->requestType = $requestType;
    }

    /**
     * Runs the attached controller for this path
     * @return Path
     */
    public function runController(){
        return $this->controller->run();
    }

    /**
     * Sets a alias name for this path
     * @return Path
     */
    public function name($string){
        $this->name = $string;
        return $this;
    }

}