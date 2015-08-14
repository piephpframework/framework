<?php

class Object69{

    public static $controllers = [];
    public static $services    = [];

    /**
     *
     * @param string $name
     * @param array $depend
     * @return \App
     */
    public static function module($name, array $depend){
        return new App($name, $depend);
    }

}

class App{

    public
            $items       = [],
            $classes     = [],
            $app         = null;
    protected
            $name     = '',
            $depend   = [],
            $services = [],
            $scope    = null;

    public function __set($name, $value){
        if($name == 'classes'){
            $this->classes = $value;
        }else{
            $this->items[$name] = $value;
        }
    }

    public function __get($name){
        if($name == 'classes'){
            return $this->classes;
        }
        if(isset($this->items[$name])){
            return $this->items[$name];
        }
        return '';
    }

    public function __call($name, $arguments){
        if(isset($this->items[$name])){
            return call_user_func_array($this->items[$name], $arguments);
        }
        if(isset($this->classes[$name])){
            return call_user_func_array($this->classes[$name], $arguments);
        }
    }

    public function __construct($name, array $depend){
        $this->name = $name;

        $apps = [];
        foreach($depend as $d){
            $class    = '\\Modules\\' . $d . '\\' . $d;
            $apps[$d] = (new $class())->init($this);
        }
        $this->depend = $apps;
    }

    public function __destruct(){
        foreach($this->depend as $dep){
            if(is_callable(array($dep, 'cleanup'))){
                $result = $dep->cleanup();
                if($result instanceof Event){
                    $this->fireEvent($result);
                }
            }
        }
    }

    /**
     * Fires off an event to anything that is listening
     * @param Event $event
     */
    public function fireEvent(Event $event){
        foreach($this->depend as $dep){
            if(is_callable(array($dep, $event->name))){
                call_user_func_array(array($dep, $event->name), array($event->value));
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
     * @return \App
     */
    public function config(callable $callback){
        $cbParams = $this->_getCbParams($callback);
        call_user_func_array($callback, $cbParams);
        return $this;
    }

    /**
     * Creates a controller to be used within the app
     * @param type $name
     * @param callable $callback
     * @return \App
     */
    public function controller($name, callable $callback){
        Object69::$controllers[$name]['controller'] = $callback;
        Object69::$controllers[$name]['scope']      = new Scope();
        Object69::$controllers[$name]['call']       = null;
        return $this;
    }

    /**
     * Creates a service to be used within the app
     * @param type $name
     * @param mixed $object
     * @return \App
     */
    public function service($name, $object){
        Object69::$services[$name] = $object;
        return $this;
    }

    /**
     * Runs a controller and only exectues it if it has yet to execute
     * @param string $name
     * @return Call
     */
    public function call($name){
        if(isset(Object69::$controllers[$name]) && !Object69::$controllers[$name]['call']){
            $scope                                = null;
            $result                               = $this->_execController($name, $scope);
            $call                                 = Object69::$controllers[$name]['call'] = new Call($scope, $result);
        }elseif(isset(Object69::$controllers[$name]) && Object69::$controllers[$name]['call']){
            $call = Object69::$controllers[$name]['call'];
        }else{
            $call = new Call();
        }
        return $call;
    }

    /**
     * Runs a controller and always executes it
     * @param string $name
     * @return Call
     */
    public function exec($name){
        if(is_array($name)){
            $ctrlName = $name['controller'];
        }else{
            $ctrlName = $name;
        }
        if(isset(Object69::$controllers[$ctrlName])){
            $scope                                    = null;
            $result                                   = App::_execController($ctrlName, $scope);
            $call                                     = Object69::$controllers[$ctrlName]['call'] = new Call($scope, $result);
        }else{
            $call = new Call();
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

    public function getServices(){
        return $this->services;
    }

    /**
     * Gets the scope
     * @return type
     */
    public function scope(){
        return $this->scope;
    }

    protected function _execController($name, &$scope = null){
        $cbParams = $this->_getCbParams(Object69::$controllers[$name], $scope);
        $result   = call_user_func_array(Object69::$controllers[$name]['controller'], $cbParams);

        return $result;
    }

    protected function _getCbParams($item, &$scope = null){
        if(is_array($item)){
            $func  = $item['controller'];
            $scope = isset($item['scope']) ? $item['scope'] : null;
        }else{
            $func = $item;
        }
        $rf       = new ReflectionFunction($func);
        $params   = $rf->getParameters();
        $cbParams = array();

//        var_dump($this->name, $this->depend, $params);

        foreach($params as $param){
            if($param->name == 'scope'){
                $cbParams[] = $scope;
            }else{
                // Inject application classes
                foreach($this->classes as $index => $class){
                    if($index == $param->name){
                        $cbParams[] = $class;
                    }
                }

                // Inject custom services
                foreach(Object69::$services as $index => $service){
                    if($index == $param->name){
                        if($service instanceof Closure){
                            $args       = $this->_getCbParams($service);
                            $cbParams[] = call_user_func_array($service, $args);
                        }else{
                            $cbParams[] = $service;
                        }
                    }
                }

                // Inject class dependencies
                foreach($this->depend as $name => $dep){
                    if($name == $param->name){
                        $cbParams[] = $dep;
                    }else{
                        $classes = $dep->getClasses();
                        foreach($classes as $index => $class){
                            if($index == $param->name){
                                $cbParams[] = $class;
                            }
                        }
                    }
//                    var_dump($param->name, $name, $classes);
//                    if(isset($classes[$param->name])){
//                        $cbParams[] = $classes[$param->name];
//                    }elseif(isset($this->depend[$param->name])){
//                        $cbParams[] = $dep;
//                    }
                }
            }
        }

        return $cbParams;
    }

}
