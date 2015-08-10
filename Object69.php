<?php

class Object69{

    public static $controllers = [];

    public static function module($name, array $depend = []){
        return new App($name, $depend);
    }

}

class App{

    public
            $classes   = [],
            $items     = [],
            $app       = null;
    protected
            $name   = '',
            $depend = [],
            $scope  = null;

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
            $apps[$d] = (new $class())->init();
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

    public function fireEvent(Event $event){
        foreach($this->depend as $dep){
            if(is_callable(array($dep, $event->name))){
                call_user_func_array(array($dep, $event->name), array($event->value));
            }
        }
    }

    public function getName(){
        return $this->name;
    }

    public function config(callable $callback){
        $cbParams = $this->_getCbParams($callback);
        call_user_func_array($callback, $cbParams);
        return $this;
    }

    public function controller($name, callable $callback){
        Object69::$controllers[$name]['controller'] = $callback;
        Object69::$controllers[$name]['scope']      = new Scope();
        Object69::$controllers[$name]['call']       = null;
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
            $result                                   = $this->_execController($ctrlName, $scope);
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
            $scope = $item['scope'];
        }else{
            $func = $item;
        }
        $rf       = new ReflectionFunction($func);
        $params   = $rf->getParameters();
        $cbParams = array();
//        var_dump($this);

        foreach($params as $param){
            if($param->name == 'scope'){
                $cbParams[] = $scope;
            }else{
                foreach($this->getClasses() as $index => $class){
                    if($index == $param->name){
                        $cbParams[] = $class;
                    }
                }
                foreach($this->depend as $dep){
                    $classes = $dep->getClasses();
                    if(isset($classes[$param->name])){
                        $cbParams[] = $classes[$param->name];
                    }elseif(isset($this->depend[$param->name])){
                        $cbParams[] = $dep;
                    }
                }
            }
        }

        return $cbParams;
    }

}
