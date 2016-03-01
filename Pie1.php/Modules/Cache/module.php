<?php

use Object69\Core\Object69;
use Object69\Core\Scope;

return call_user_func(function(){
    $app = Object69::module('Cache');

    $app->service('Cache', new Cache());

    return $app;
});