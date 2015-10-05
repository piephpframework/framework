<?php

namespace Object69\Core;

use ReflectionFunction;
use ReflectionMethod;

/**
 * @property App $parent The parent App
 */
class App{

    protected
            $name        = '',
            $apps        = [],
            $controllers = [],
            $directives  = [],
            $services    = [],
            $filters     = [],
            $parent      = null;

    public function __construct($name, array $dependencies){
        $this->name = $name;
        $apps       = [];

        foreach($dependencies as $dependName){
            $modules = glob(__DIR__ . '/../Modules/*', GLOB_ONLYDIR);
            foreach($modules as $module){
                $moduleName = basename($module);
                $app        = $this->loadModule($dependName, $moduleName, $module);
                if($app instanceof App){
                    $apps[$dependName] = $app;
                }
            }
            $base        = isset($_ENV['root']['modules']) ? $_ENV['root']['modules'] : '.';
            $modulesBase = strpos($base, '/') === 0 ? $base : $_SERVER['DOCUMENT_ROOT'] . '/' . $base;
            $userModules = glob($modulesBase . '/*', GLOB_ONLYDIR);
            foreach($userModules as $module){
                $moduleName = basename($module);
                $app        = $this->loadModule($dependName, $moduleName, $module);
                if($app instanceof App){
                    $apps[$dependName] = $app;
                }
            }
        }
        $this->apps = $apps;
    }

    protected function loadModule($dependName, $moduleName, $module){
        if(strtolower($dependName) == strtolower($moduleName)){
            /* @var $app App */
            $app = require_once $module . '/module.php';

            $app->service('rootScope', Object69::$rootScope);
            $app->service('env', new Env());
            $app->setParent($this);

            return $app;
        }
        return null;
    }

    public function __destruct(){
        foreach($this->apps as $name => $dep){
            $result = $dep->cleanup($this);
            if($result instanceof Event){
                $this->fireEvent($result);
            }
        }
    }

    public function __call($name, $arguments){
        if(isset($this->$name) && is_callable($this->$name)){
            $call = $this->$name->bindTo($this, $this);
            return call_user_func_array($call, $arguments);
        }
    }

    public function setParent($parent){
        $this->parent = $parent;
    }

    public function getParent(){
        return $this->parent;
    }

    /**
     * Fires off an event to listening dependencies
     * @param Event $event
     */
    public function fireEvent(Event $event){
        foreach($this->apps as $dep){
            if(isset($dep->{$event->name}) && is_callable($dep->{$event->name})){
                $call = $dep->{$event->name}->bindTo($dep, $dep);
                call_user_func_array($call, [$event->value, $this]);
            }
        }
    }

    /**
     * Gets the name of the app
     * @return type
     */
    public function getName(){
        return $this->name;
    }

    /**
     * Executes the configuration
     * @param callable $callback
     * @return App
     */
    public function config(callable $callback){
        $call     = $callback->bindTo($this, $this);
        $cbParams = $this->_getCbParams($callback);
        call_user_func_array($call, $cbParams);
        return $this;
    }

    /**
     * Creates a controller to be used within the app
     * @param string $name
     * @param callable|string $callback
     * @param string $method
     * @return App
     */
    public function controller($name, $callback, $method = null){
//        $callname = $name;
//        $pos      = strrpos($name, '\\');
//        if($pos > 0){
//            $callname = substr($name, $pos + 1);
//        }

        $this->controllers[$name] = new Controller($name, $callback, $method);
        $this->controllers[$name]->setScope(new Scope());
        return $this->controllers[$name];
//        if(is_callable($callback)){
//            $this->controllers[$callname]['controller'] = $callback;
//        }elseif(is_string($callback)){
//            $this->controllers[$callname]['controller'] = new $name();
//            $this->controllers[$callname]['method']     = $callback;
//        }else{
//            throw new Exception('Invalid callback, must be a callable or a string');
//        }
//        $this->controllers[$callname]['scope'] = new Scope();
//        $this->controllers[$callname]['call']  = null;
//        return $this;
    }

    /**
     * Creates a service to be used within the app
     * @param string $name
     * @param mixed $object
     * @return App
     */
    public function service($name, $object){
        if(is_callable($object)){
            $cbParams              = $this->_getCbParams($object);
            $this->services[$name] = call_user_func_array($object, $cbParams);
        }else{
            $this->services[$name] = $object;
        }
        return $this;
    }

    /**
     * Creates a directive to be used within the app
     * @param string $name
     * @param mixed $object
     * @return App
     */
    public function directive($name, $object){
        $call                    = $object->bindTo($this, $this);
        $cbParams                = $this->_getCbParams($object);
        $this->directives[$name] = call_user_func_array($call, $cbParams);
        return $this;
    }

    /**
     * Creates a filter that can be used in the template tool
     * @param string $name
     * @param mixed $object
     * @return App
     */
    public function filter($name, $object){
        $call                 = $object->bindTo($this, $this);
        $cbParams             = $this->_getCbParams($object);
        $this->filters[$name] = call_user_func_array($call, $cbParams);
        return $this;
    }

    /**
     * Calls a function
     * @param type $name
     * @return Call
     */
    public function call($name, $parent = null){
        $current = $parent === null ? $this : $parent;
        foreach($current->getControllers() as $ctrlName => $controller){
            if($ctrlName == $name){
                return $current->runController($controller);
            }
        }
        foreach($current->getApps() as $app){
            foreach($app->getControllers() as $ctrlName => $controller){
                if($ctrlName == $name){
                    if(($controller instanceof Controller && !$controller->call) || !$controller['call']){
                        return $app->runController($controller);
                    }else{
                        return $controller['call'];
                    }
                }
            }
        }

        if($current->parent !== null){
            return $current->call($name, $current->getParent());
        }
        return new Call();

//        if(isset($this->controllers[$name])){
//            if(!$this->controllers[$name]['call']){
//                $call = $this->runController($this->controllers[$name]);
//            }else{
//                $call = $this->controllers[$name]['call'];
//            }
//            if($call instanceof Call){
//                return $call;
//            }
//        }
//        foreach($this->getApps() as $app){
//            $controllers = $app->getControllers();
//            if(isset($controllers[$name])){
//                if(!$controllers[$name]['call']){
//                    $call = $this->runController($controllers[$name]);
//                    return $call;
//                }elseif($controllers[$name]['call']){
//                    $call = $controllers[$name]['call'];
//                }
//            }
//        }
//        if(!isset($call)){
//            return new Call();
//        }
//        return $call;
    }

    public function exec($controller){
//        if(is_array($name)){
//            $name = $name['controller'];
//        }

        if($controller instanceof Controller){
            $call = $this->runController($controller);
        }
//        if(isset($this->controllers[$name])){
//            $call = $this->runController($this->controllers[$name]);
//        }
        else{
            $call = new Call();
        }
        return $call;
    }

    /**
     * Runs a controller
     * @param Controller $controller
     * @return Call
     */
    protected function runController(Controller $controller){
        $call = null;
        if($controller){
            $scope  = null;
            $result = $this->execController($controller, $scope);
            $controller->setCall($call   = new Call($scope, $result));
        }
        return $call;
    }

    /**
     * Gets a list of the applications classes
     * @return array
     */
    public function getClasses(){
        return $this->classes;
    }

    public function getControllers(){
        return $this->controllers;
    }

    public function getServices(){
        return $this->services;
    }

    public function getDirectives(){
        return $this->directives;
    }

    public function getFilters(){
        return $this->filters;
    }

    public function getApps(){
        return $this->apps;
    }

    /**
     * Runs a particular controller
     * @param Controller $controller The controller
     * @param type $scope
     * @return type
     */
    public function execController(Controller $controller, &$scope = null){
        $cbParams = $this->_getCbParams($controller, $scope);
        if($controller->method !== null){
            $result = call_user_func_array([$controller->controller, $controller->method], $cbParams);
        }else{
            $result = call_user_func_array($controller->controller, $cbParams);
        }
        return $result;
    }

    protected function _getCbParams($controller, &$scope = null){
        if(is_array($controller)){
            $func  = $controller['controller'];
            $scope = $controller['controller']->getScope(); //isset($controller['scope']) ? $controller['scope'] : null;
        }else{
            $func = $controller;
            if($func instanceof Controller){
                $scope = $func->getScope();
            }
        }

        if($func instanceof Controller && $func->method !== null){
            $rf = new ReflectionMethod($func->controller, $func->method);
        }elseif($func instanceof Controller && $func->method === null){
            $rf = new \ReflectionFunction($func->controller);
        }else{
            $rf = new ReflectionFunction($func);
        }
        $params   = $rf->getParameters();
        $cbParams = [];
        foreach($params as $param){
            if($param->name == 'scope'){
                $cbParams[] = $scope;
            }elseif($param->name == 'rootScope'){
                $cbParams[] = Object69::$rootScope;
            }else{
                $cbParams[] = $this->paramLookup($param->name);
            }
        }
        return $cbParams;
    }

    protected function paramLookup($pname, $parent = null){
        /* @var $current App */
        $current = $parent === null ? $this : $parent;
        // Inject Services From Current App
        foreach($current->getServices() as $serviceName => $service){
            if($pname == $serviceName){
                return $service;
            }
        }
        /* @var $depend App */
        foreach($current->getApps() as $depend){
            // Inject Registered Services
            $services = $depend->getServices();
            foreach($services as $serviceName => $service){
                if($pname == $serviceName){
                    return $service;
                }
            }
        }
        if($current->parent !== null){
            return $this->paramLookup($pname, $current->parent);
        }
        return null;
    }

}
