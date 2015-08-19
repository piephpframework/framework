<?php

function lower($value){
    return strtolower($value);
}

function upper($value){
    return strtoupper($value);
}

function encode($value, $type = 'md5'){
    return hash($type, $value);
}

function json($value){
    return json_encode($value);
}

function def($value, $default = ''){
    return !empty($value) ? $value : $default;
}
