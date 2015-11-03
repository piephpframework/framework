<?php

use Pie\Pie;
use Pie\Modules\Http\Http;

return call_user_func(function(){
    $app = Pie::module('Http', []);

    $app->service('http', new Http());

    return $app;
});
