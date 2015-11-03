<?php

namespace Pie\Crust;

class Call{

    protected $scope  = null, $result = null;

    public function __construct($scope = null, $result = null){
        $this->scope  = $scope;
        $this->result = $result;
    }

    public function scope(){
        return $this->scope;
    }

    public function result(){
        return $this->result;
    }

}
