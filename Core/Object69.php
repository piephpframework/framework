<?php

namespace Object69\Core;

class Object69{

//    public static $controllers = [];
//    public static $services    = [];
//    public static $directives  = [];
    public static $root      = null;
    public static $rootScope = null;

    /**
     *
     * @param string $name
     * @param array $depend
     * @return App
     */
    public static function module($name, array $depend){
        return new App($name, $depend);
    }

    public static function find($path, $obj){
        for($i = 0, $path = preg_split('/[\[\]\.]/', $path), $len = count($path); $i < $len; $i++){
            if($path[$i] == ''){
                continue;
            }
            if($path[$i] == '$server'){
                $obj = $_SERVER;
            }elseif($path[$i] == '$session'){
                $obj = $_SESSION;
            }elseif($path[$i] == '$env'){
                $obj = $_ENV;
            }elseif($path[$i] == '$get'){
                $obj = $_GET;
            }elseif($path[$i] == '$post'){
                $obj = $_POST;
            }elseif($path[$i] == '$request'){
                $obj = $_REQUEST;
            }elseif($path[$i] == '$cookie'){
                $obj = $_COOKIE;
            }elseif($path[$i] == '$root'){
                $obj = Object69::$rootScope;
            }else{
                $item = ctype_digit($path[$i]) ? (int)$path[$i] : $path[$i];
                if(is_object($obj)){
                    $obj = $obj->$item;
                }else{
                    $obj = isset($obj[$item]) ? $obj[$item] : '';
                }
            }
        }
        return $obj;
    }

}

Object69::$rootScope = new RootScope();

//Object69::$root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
//
//Object69::$services['env'] = new Env();
//Object69::$rootScope       = new RootScope();
//if(isset($_ENV['session']['use']) && $_ENV['session']['use'] == 'yes'){
//    Object69::$services['session'] = new Session();
//}

//class App{
//
//    public
//        $items       = [],
//        $classes     = [],
//        $app         = null;
//    protected
//        $name     = '',
//        $depend   = [],
//        $services = [],
//        $scope    = null;
//
//    public function __set($name, $value){
//        if($name == 'classes'){
//            $this->classes = $value;
//        }else{
//            $this->items[$name] = $value;
//        }
//    }
//
//    public function __get($name){
//        if($name == 'classes'){
//            return $this->classes;
//        }
//        if(isset($this->items[$name])){
//            return $this->items[$name];
//        }
//        return '';
//    }
//
//    public function __call($name, $arguments){
//        if(isset($this->items[$name])){
//            return call_user_func_array($this->items[$name], $arguments);
//        }
//        if(isset($this->classes[$name])){
//            return call_user_func_array($this->classes[$name], $arguments);
//        }
//    }
//
//    public function __construct($name, array $depend){
//        $this->name = $name;
//
//        $apps = [];
//        foreach($depend as $class){
//            $new         = new $class();
//            $name        = array_pop(explode('\\', $class));
//            $apps[$name] = $new->init($this);
//        }
//        $this->depend = $apps;
//    }
//
//    public function __destruct(){
//        foreach($this->depend as $dep){
//            if(is_callable(array($dep, 'cleanup'))){
//                $result = $dep->cleanup();
//                if($result instanceof Event){
//                    $this->fireEvent($result);
//                }
//            }
//        }
//    }
//
//    /**
//     * Fires off an event to anything that is listening
//     * @param Event $event
//     */
//    public function fireEvent(Event $event){
//        foreach($this->depend as $dep){
//            if(is_callable(array($dep, $event->name))){
//                call_user_func_array(array($dep, $event->name), array($event->value));
//            }
//        }
//    }
//
//    /**
//     * Gets the name of the app
//     * @return type
//     */
//    public function getName(){
//        return $this->name;
//    }
//
//    /**
//     * Executes the configuration
//     * @param callable $callback
//     * @return App
//     */
//    public function config(callable $callback){
//        $cbParams = $this->_getCbParams($callback);
//        call_user_func_array($callback, $cbParams);
//        return $this;
//    }
//
//    /**
//     * Creates a controller to be used within the app
//     * @param string $name
//     * @param callable|string $callback
//     * @param string $method
//     * @return App
//     */
//    public function controller($name, $callback, $method = null){
//        if(is_callable($callback)){
//            Object69::$controllers[$name]['controller'] = $callback;
//        }elseif(is_string($callback)){
//            Object69::$controllers[$name]['controller'] = new $callback();
//            Object69::$controllers[$name]['method']     = $method;
//        }else{
//            throw new Exception('Invalid callback, must be a callable or a string');
//        }
//        Object69::$controllers[$name]['scope'] = new Scope();
//        Object69::$controllers[$name]['call']  = null;
//        return $this;
//    }
//
//    /**
//     * Creates a service to be used within the app
//     * @param string $name
//     * @param mixed $object
//     * @return App
//     */
//    public function service($name, $object){
//        Object69::$services[$name] = $object;
//        return $this;
//    }
//
//    /**
//     * Creates a directive to be used within the app
//     * @param string $name
//     * @param mixed $object
//     * @return App
//     */
//    public function directive($name, $object){
//        Object69::$directives[$name] = $object;
//        return $this;
//    }
//
//    /**
//     * Runs a controller and only exectues it if it has yet to execute
//     * @param string $name
//     * @return Call
//     */
//    public function call($name){
//        if(isset(Object69::$controllers[$name]) && !Object69::$controllers[$name]['call']){
//            $scope                                = null;
//            $result                               = $this->execController($name, $scope);
//            $call                                 = Object69::$controllers[$name]['call'] = new Call($scope, $result);
//        }elseif(isset(Object69::$controllers[$name]) && Object69::$controllers[$name]['call']){
//            $call = Object69::$controllers[$name]['call'];
//        }else{
//            $call = new Call();
//        }
//        return $call;
//    }
//
//    /**
//     * Runs a controller and always executes it
//     * @param string $name
//     * @return Call
//     */
//    public function exec($name){
//        if(is_array($name)){
//            $ctrlName = $name['controller'];
//        }else{
//            $ctrlName = $name;
//        }
//        if(isset(Object69::$controllers[$ctrlName])){
//            $scope                                    = null;
//            $result                                   = App::execController($ctrlName, $scope);
//            $call                                     = Object69::$controllers[$ctrlName]['call'] = new Call($scope, $result);
//        }else{
//            $call = new Call();
//        }
//        return $call;
//    }
//
//    /**
//     * Gets a list of the applications classes
//     * @return array
//     */
//    public function getClasses(){
//        return $this->classes;
//    }
//
//    public function getServices(){
//        return $this->services;
//    }
//
//    /**
//     * Gets the scope
//     * @return type
//     */
//    public function scope(){
//        return $this->scope;
//    }
//
//    /**
//     * Runs a particular controller
//     * @param string $name The name of the controller
//     * @param type $scope
//     * @return type
//     */
//    public function execController($name, &$scope = null){
//        $cbParams = $this->_getCbParams(Object69::$controllers[$name], $scope);
//        $class    = Object69::$controllers[$name]['controller'];
//        if(is_object(Object69::$controllers[$name]['controller']) && isset(Object69::$controllers[$name]['method'])){
//            $method = Object69::$controllers[$name]['method'];
//            $result = call_user_func_array([$class, $method], $cbParams);
//        }else{
//            $result = call_user_func_array($class, $cbParams);
//        }
//        return $result;
//    }
//
//    /**
//     * Gets the needed classes/variables from a controller
//     * @param mixed $item
//     * @param type $scope
//     * @return type
//     */
//    protected function _getCbParams($item, &$scope = null){
//        if(is_array($item)){
//            $func  = $item['controller'];
//            $scope = isset($item['scope']) ? $item['scope'] : null;
//        }else{
//            $func = $item;
//        }
//        if(is_object($func) && is_array($item) && isset($item['method'])){
//            $rf = new ReflectionMethod($func, $item['method']);
//        }else{
//            $rf = new ReflectionFunction($func);
//        }
//        $params   = $rf->getParameters();
//        $cbParams = array();
//
//        foreach($params as $param){
//            if($param->name == 'scope'){
//                $cbParams[] = $scope;
//            }elseif($param->name == 'rootScope'){
//                $cbParams[] = Object69::$rootScope;
//            }else{
//                // Inject application classes
//                foreach($this->classes as $index => $class){
//                    if($index == $param->name){
//                        $cbParams[] = $class;
//                    }
//                }
//
//                // Inject custom services
//                foreach(Object69::$services as $index => $service){
//                    if($index == $param->name){
//                        if($service instanceof Closure){
//                            $args       = $this->_getCbParams($service);
//                            $cbParams[] = call_user_func_array($service, $args);
//                        }else{
//                            $cbParams[] = $service;
//                        }
//                    }
//                }
//
//                // Inject class dependencies
//                foreach($this->depend as $name => $dep){
//                    if($name == $param->name){
//                        $cbParams[] = $dep;
//                    }else{
//                        $classes = $dep->getClasses();
//                        foreach($classes as $index => $class){
//                            if($index == $param->name){
//                                $cbParams[] = $class;
//                            }
//                        }
//                    }
//                }
//            }
//        }
//
//        return $cbParams;
//    }
//
//}
