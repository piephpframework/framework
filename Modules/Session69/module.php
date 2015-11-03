<?php

use Pie\Modules\Session69\Session;
use Pie\Crust\Pie;

return call_user_func(function(){
    $app = Pie::module('Session69', []);

    $app->service('session', new Session());

    return $app;
});
