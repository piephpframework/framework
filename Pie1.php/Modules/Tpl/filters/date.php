<?php

$app->filter('date', function(){
    return function ($value, $format){
        $time = !ctype_digit($value) ? strtotime($value) : $value;
        switch($format){
            case 'medium':
                $format = 'M d, Y h:i:s a';
                break;
            case 'short':
                $format = 'm/d/y h:i a';
                break;
            case 'fullDate':
                $format = 'l, F j, Y';
                break;
            case 'longDate':
                $format = 'F j, Y';
                break;
            case 'mediumDate':
                $format = 'M j, Y';
                break;
            case 'shortDate':
                $format = 'm/d/y';
                break;
            case 'mediumTime':
                $format = 'h:i:s a';
                break;
            case 'shortTime':
                $format = 'h:i a';
                break;
            default :
                if($format === null){
                    $format = 'Y-m-d H:i:s';
                }
                break;
        }
        return date($format, $time);
    };
});
