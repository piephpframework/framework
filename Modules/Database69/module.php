<?php

use Object69\Core\Object69;
use Object69\Modules\Database69\Db;

return call_user_func(function(){
    $app = Object69::module('Database69', []);

    $app->service('db', new Db());

//    $app->exposedClasses = [
//        'db' => new Db()
//    ];

    return $app;
});
