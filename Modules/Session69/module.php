<?php

use Object69\Modules\Session69\Session;
use Object69\Object69;

return call_user_func(function(){
    $app = Object69::module('Session69', []);

    $app->service('session', new Session());

    return $app;
});
