<?php

/**
 * Converts a string to lowercase
 */
$app->filter('lower', function(){
    return function ($value){
        return strtolower($value);
    };
});

/**
 * Converts a string to uppercase
 */
$app->filter('upper', function(){
    return function ($value){
        return strtoupper($value);
    };
});

/**
 * Uppercase the first character of each word in a string
 */
$app->filter('ucwords', function(){
    return function ($value){
        return ucwords($value);
    };
});

/**
 * Make a string's first character uppercase
 */
$app->filter('ucfirst', function(){
    return function ($value){
        return ucfirst($value);
    };
});

/**
 * Reverses a string
 */
$app->filter('reverse', function(){
    return function ($value){
        return ucfirst($value);
    };
});

/**
 * Encodes a string to a supported hash type
 */
$app->filter('hash', function(){
    return function ($value, $type = 'md5'){
        return hash($type, $value);
    };
});

/**
 * Json encodes a string
 */
$app->filter('json', function(){
    return function ($value){
        return json_encode($value);
    };
});

/**
 * Uses a default value if one is not defined
 */
$app->filter('def', function(){
    return function ($value, $default = ''){
        return !empty($value) ? $value : $default;
    };
});

/**
 * Converts new lines to br tags
 */
$app->filter('nl2br', function(){
    return function ($value){
        return nl2br($value);
    };
});

/**
 * Trims whitespace from the beginning and end of a string
 */
$app->filter('trim', function(){
    return function ($value){
        return trim($value);
    };
});

/**
 * Trims whitespace from the beginning of a string
 */
$app->filter('ltrim', function(){
    return function ($value){
        return ltrim($value);
    };
});

/**
 * Trims whitespace from the end of a string
 */
$app->filter('rtrim', function(){
    return function ($value){
        return rtrim($value);
    };
});

/**
 * Strips html tags
 */
$app->filter('strip', function(){
    return function ($value){
        return strip_tags($value);
    };
});

/**
 * Converts all applicable characters to HTML entities
 */
$app->filter('entities', function(){
    return function ($value){
        return htmlentities($value);
    };
});

/**
 * URL-encodes a string
 */
$app->filter('encode', function(){
    return function ($value){
        return urlencode($value);
    };
});

/**
 * Decodes a URL-encoded string
 */
$app->filter('decode', function(){
    return function ($value){
        return urldecode($value);
    };
});
