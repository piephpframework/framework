<?php

namespace Application;

use stdClass;

class Object {

    protected $meta = null;

    /**
     * Gets the saved meta
     * @return mixed The meta object containing the metadata
     */
    public function getMeta(){
        return $this->meta;
    }

    /**
     * Sets the metadata
     * @param mixed $meta The metadata to set
     * @return Object
     */
    public function setMeta($meta){
        $this->meta = $meta;
        return $this;
    }

}