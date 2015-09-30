<?php

use Object69\Core\Event;
use Object69\Core\Object69;
use Object69\Modules\Route69\Route;
use Object69\Modules\Route69\RouteParams;

return call_user_func(function(){
    $app = Object69::module('Route69', []);

    $route       = new Route();
    $routeParams = new RouteParams();

    $app->service('route', $route);
    $app->service('routeParams', $routeParams);

//    $app->exposedClasses = [
//        'route'       => $route,
//        'routeParams' => new RouteParams()
//    ];

    $app->method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
    $app->path   = $route->pathToArray(filter_input(INPUT_SERVER, 'REQUEST_URI'));

    $app->cleanup = function($parent) use($route){
        $controller = $route->findRoute($this);
        if($controller !== null){
            $parent->exec($controller);
        }
        $event        = new Event();
        $event->name  = 'routeChange';
        $event->value = [$controller, $route->getAlways()];

        return $event;
    };

    $app->routeChange = function($value, $parent) use($route){
        if(isset($route->getAlways()['displayAs'])){
            $route->executeController($parent, $value[0]);
        }
    };

    return $app;
});
