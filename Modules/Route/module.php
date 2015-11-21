<?php

use Pie\Crust\Controller;
use Pie\Crust\Event;
use Pie\Pie;
use Pie\Modules\Route\Route;
use Pie\Modules\Route\RouteParams;

return call_user_func(function(){
    $app = Pie::module('Route');

    $route       = new Route();
    $routeParams = new RouteParams();

    $app->service('route', $route);
    $app->service('routeParams', $routeParams);

    $app->method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
    $app->path   = $route->pathToArray(rtrim(filter_input(INPUT_SERVER, 'REQUEST_URI'), '/'));
    $app->query  = $route->queryString(filter_input(INPUT_SERVER, 'REQUEST_URI'));

    parse_str($app->query, $_GET);

    $app->listen('cleanup', function($parent) use ($route, $app){
        $controller = $route->findRoute($app);
        if(isset($controller['settings']['modules'])){
            $parent->addDepndencies($controller['settings']['modules']);
        }
        if($controller !== null){
            $parent->exec($controller);
        }
        $app->broadcast('routeComplete', [$controller, $parent]);
    });

    $app->listen('routeComplete', function($controller, $parent) use ($route){
        if(isset($route->getAlways()['displayAs']) || isset($controller['settings']['displayAs']) || isset($controller['settings']['controller'])){
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
    });

    return $app;
});
