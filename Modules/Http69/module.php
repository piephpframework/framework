<?php

use Pie\Crust\Pie;
use Pie\Modules\Http69\Http;

return call_user_func(function(){
    $app = Pie::module('Http69', []);

    $app->service('http', new Http());

    return $app;
});
