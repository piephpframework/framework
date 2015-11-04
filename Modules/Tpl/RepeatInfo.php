<?php

/**
 *
 * @author Ryan Naddy <rnaddy@corp.acesse.com>
 * @name RepeatInfo.php
 * @version 1.0.0 Oct 1, 2015
 */

namespace Pie\Modules\Tpl;

/**
 * @property int $length Length of the repeating item
 */
class RepeatInfo{

    protected $length   = 0;
    protected $offset   = 0;
    protected $repeater = '';

    public function __construct($length, $repeater){
        $this->length = $length;
        $this->repeater = $repeater;
    }

    public function __get($name){
        return $this->$name;
    }

    public function incrementOffset($amount = 1){
        $this->offset += $amount;
    }

    public function setRepeater($value){
        $this->repeater = $value;
    }

    public function getRepeater(){
        return $this->repeater;
    }

}
