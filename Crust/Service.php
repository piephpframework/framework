<?php

namespace Object69\Core;

abstract class Service{

    protected $values = [];

    protected function _find($path, $object = null){
        $obj = $object === null ? $this->values : $object;
        return Object69::find($path, $obj);
    }

}
