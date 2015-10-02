<?php

$app->filter('lower', function(){
    return function ($value){
        return strtolower($value);
    };
});

$app->filter('upper', function(){
    return function ($value){
        return strtoupper($value);
    };
});

$app->filter('encode', function(){
    return function ($value, $type = 'md5'){
        return hash($type, $value);
    };
});

$app->filter('json', function(){
    return function ($value){
        return json_encode($value);
    };
});

$app->filter('def', function(){
    return function ($value, $default = ''){
        return !empty($value) ? $value : $default;
    };
});

$app->filter('nl2br', function(){
    return function ($value){
        return nl2br($value);
    };
});
