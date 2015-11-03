<?php

// Initialize default environment variables
$_ENV = [
    'root'    => [
        'source'    => '.',
        'templates' => '.',
        'modules'   => '.'
    ],
    'session' => [
        'use' => 'no'
    ]
];

spl_autoload_register(function($class){
    $class      = preg_replace('/^Pie\\\/', '', $class);
    $base       = isset($_ENV['root']['source']) ? $_ENV['root']['source'] : '.';
    $sourceRoot = strpos($base, '/') === 0 ? $base : $_SERVER['DOCUMENT_ROOT'] . '/' . $base;
    // Attpempt to load from the source root
    $filename   = $sourceRoot . '/' . str_replace('\\', '/', $class) . '.php';
    if(is_file($filename)){
        require_once $filename;
        return;
    }

    // Look starting from the current directory
    $filename = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if(is_file($filename)){
        require_once $filename;
        return;
    }

    // Look starting from the document root
    $filename = $_SERVER['DOCUMENT_ROOT'] . '/' . str_replace('\\', '/', $class) . '.php';
    if(is_file($filename)){
        require_once $filename;
        return;
    }
});
