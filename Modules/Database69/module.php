<?php

use Pie\Crust\Pie;
use Pie\Modules\Database69\Db;

return call_user_func(function(){
    $app = Pie::module('Database69', []);

    $app->service('db', new Db());

    return $app;
});
