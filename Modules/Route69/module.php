<?php

use Object69\Core\Controller;
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

    $app->method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
    $app->path   = $route->pathToArray(filter_input(INPUT_SERVER, 'REQUEST_URI'));
    $app->query  = $route->queryString(filter_input(INPUT_SERVER, 'REQUEST_URI'));

    parse_str($app->query, $_GET);

    $app->cleanup = function($parent) use($route){
        $controller = $route->findRoute($this);
        if($controller !== null){
            $parent->exec($controller);
        }
        $event        = new Event();
        $event->name  = 'routeChange';
        $event->value = [$controller];

        return $event;
    };

    $app->routeChange = function($value, $parent) use($route){
        if(isset($route->getAlways()['displayAs']) || isset($value[0]['settings']['displayAs'])){
            $controller = $value[0];
            if(
                isset($controller['settings']['controller'])
                && is_string($controller['settings']['controller'])
                && $this->controllerExists($controller['settings']['controller'], $contrl)
            ){
                $name = $controller['settings']['controller'];
                $route->executeController($parent, $contrl);
            }elseif(
                isset($controller['settings']['controller'])
                && !($controller instanceof Controller)
                && is_string($controller['settings']['controller'])
            ){
                $controllerName = $controller['settings']['controller'];
                $method = isset($controller['settings']['method']) ? $controller['settings']['method'] : '';
                $name = trim($controllerName . '.' . $method, '.');
                $controller = new Controller($name, $controllerName, $method);
                $controller->setSettings($controller['settings']);
            }elseif($controller instanceof Controller){
                $route->executeController($parent, $controller);
            }
        }
    };

    return $app;
});
