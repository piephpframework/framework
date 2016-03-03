<?php

namespace Application;

use stdClass;

class Object {

    protected $meta = null;

    public function getMeta(){
        return $this->meta;
    }

    public function setMeta($meta){
        $this->meta = $meta;
        return $this;
    }

}