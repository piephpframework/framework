<?php

use Object69\Core\Object69;
use Object69\Modules\Media69\Media;

return call_user_func(function(){

    $app = Object69::module('Media69', []);

    $app->service('media', new Media());

    return $app;
});