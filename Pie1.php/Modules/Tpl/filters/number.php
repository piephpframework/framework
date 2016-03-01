<?php

$app->filter('number', function(){
    return function ($value, $decimals = 0){
        $value = is_object($value) ? null : $value;
        return number_format((float)$value, (int)$decimals);
    };
});

$app->filter('currency', function(){
    return function ($value, $symbol = '$', $decimals = 2){
        return $symbol . number_format((float)$value, (int)$decimals);
    };
});
