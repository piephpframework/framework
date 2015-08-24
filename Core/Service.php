<?php

namespace Object69\Core;

class Service{

    protected $values = [];

    public function find($object = null, $path = ''){
        $obj = $object === null ? $this->values : $object;
        return Object69::find($obj, $path);
    }

}
