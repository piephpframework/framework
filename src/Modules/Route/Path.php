<?php

namespace Modules\Route;

class Path {

    protected $path = '', $controller;
    protected $requestType = RequestType::All;

    public function __construct($path, $controller, $requestType){
        $this->path = $path;
        $this->controller = $controller;
        $this->requestType = $requestType;
    }

}