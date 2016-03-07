<?php

require_once __DIR__ . '/src/Application/Helper/functions.php';

spl_autoload_register(function($class){
    // Look for the file in the src
    $filename = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if(is_file($filename)){
        require_once $filename;
        return;
    }
    // Look starting from the document root
    $filename = $_SERVER['DOCUMENT_ROOT'] . '/../' . str_replace('\\', '/', $class) . '.php';
    if(is_file($filename)){
        require_once $filename;
        return;
    }
});
