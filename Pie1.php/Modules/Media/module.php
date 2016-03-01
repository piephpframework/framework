<?php

use Pie\Pie;
use Pie\Modules\Media\Media;

return call_user_func(function(){

    $app = Pie::module('Media', []);

    $app->service('media', new Media());

    return $app;
});