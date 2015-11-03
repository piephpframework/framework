<?php

use Pie\Crust\Pie;
use Pie\Modules\Media69\Media;

return call_user_func(function(){

    $app = Pie::module('Media69', []);

    $app->service('media', new Media());

    return $app;
});