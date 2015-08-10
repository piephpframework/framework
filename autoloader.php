<?php

spl_autoload_register(function($class){
    $filename = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if(is_file($filename)){
        require_once $filename;
    }
});
