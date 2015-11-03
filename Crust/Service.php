<?php

namespace Pie\Crust;

use Pie\Pie;

abstract class Service{

    protected $values = [];

    protected function _find($path, $object = null){
        $obj = $object === null ? $this->values : $object;
        return Pie::find($path, $obj);
    }

}
