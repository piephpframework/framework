<?php

/**
 *
 * @author Ryan Naddy <rnaddy@corp.acesse.com>
 * @name RepeatInfo.php
 * @version 1.0.0 Oct 1, 2015
 */

namespace Object69\Modules\Tpl69;

/**
 * @property int $length Length of the repeating item
 */
class RepeatInfo{

    protected $length = 0;

    public function __construct($length){
        $this->length = $length;
    }

    public function __get($name){
        return $this->$name;
    }

}
