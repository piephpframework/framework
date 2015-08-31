<?php

use Object69\Core\Object69;
use Object69\Modules\Http69\Http;

return call_user_func(function(){
    $app = Object69::module('Http69', []);

    $app->service('http', new Http());

    return $app;
});
