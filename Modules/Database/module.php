<?php

use Pie\Pie;
use Pie\Modules\Database\Db;

return call_user_func(function(){
    $app = Pie::module('Database', []);

    $app->service('db', new Db());

    return $app;
});
