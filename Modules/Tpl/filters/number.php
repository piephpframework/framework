<?php

$app->filter('number', function(){
    return function ($value, $decimals = 0){
        return number_format((float)$value, (int)$decimals);
    };
});

$app->filter('currency', function(){
    return function ($value, $symbol = '$', $decimals = 2){
        return $symbol . number_format((float)$value, (int)$decimals);
    };
});
