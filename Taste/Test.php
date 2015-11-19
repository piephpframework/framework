<?php

namespace Pie\Taste;

use Pie\Taste\Browser;

call_user_func(function(){

    $piephp_test_class = null;

    function describe($name, callable $callback){
        global $piephp_test_class;
        $piephp_test_class = new Test();
        $callback = $callback->bindTo($piephp_test_class, $piephp_test_class);
        call_user_func($callback);
    }

    function it($name, callable $callback){
        global $piephp_test_class;
        $callback = $callback->bindTo($piephp_test_class, $piephp_test_class);
        beforeEach();
        call_user_func($callback);
        return $piephp_test_class;
    }

    function first(callable $callback){
        global $piephp_test_class;
        $callback = $callback->bindTo($piephp_test_class, $piephp_test_class);
        call_user_func($callback);
    }

    function beforeEach(callable $callback = null){
        global $piephp_test_class;
        $args = func_num_args();
        if($args == 1){
            $callback = $callback->bindTo($piephp_test_class, $piephp_test_class);
            $piephp_test_class->setEach($callback);
        }elseif($args == 0 && $piephp_test_class->getEach() !== null){
            call_user_func($piephp_test_class->getEach());
        }
        return $piephp_test_class;
    }

    function expect($value){
        global $piephp_test_class;
        return $piephp_test_class->expect($value);
    }
});

class Test{

    protected $callback;
    protected $value;
    protected $each = null;

    public function expect($value){
        $this->value = $value;
        return $this;
    }

    public function setEach(callable $callback){
        $this->each = $callback;
    }

    public function getEach(){
        return $this->each;
    }

    public function toEqual($value, $strict = false){
        $error = false;
        if($strict && $value !== $this->value){
            $error = true;
        }else{
            if($value != $this->value){
                $error = true;
            }
        }
        if($error){
            // TODO: Log the error
            return false;
        }
        return true;
    }

    public function toMatch($regexp){
        if(preg_match($regexp, $this->value) === false){
            // TODO: Log the error
            return false;
        }
        return true;
    }

    public function toBeGreater(){

    }

    public function toBeLess(){

    }

}