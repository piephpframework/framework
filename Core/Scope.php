<?php

namespace Object69\Core;

/**
 * @property Scope $parentScope The parent scope
 */
class Scope{

    protected $properties  = [];
    protected $parentScope = null;

    public function __construct(array $scope = [], $parentScope = null){
        $this->properties  = $scope;
        $this->parentScope = $parentScope;
    }

    public function __set($name, $value){
        $this->properties[$name] = $value;
    }

    public function __get($name){
        if(isset($this->properties[$name])){
            return $this->properties[$name];
        }
        return '';
    }

    public function __call($name, $arguments){
        if(isset($this->properties[$name])){
            $call = $this->properties[$name];
            $call = $call->bindTo($this);
            return call_user_func_array($call, $arguments);
        }
    }

    public function setParentScope(Scope $parentScope){
        $this->parentScope = $parentScope;
    }

    /**
     * Gets the parent Scope
     * @return Scope
     */
    public function getParentScope(){
        return $this->parentScope;
    }

}
