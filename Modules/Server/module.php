<?php

use Pie\Pie;

use Pie\Modules\Server\Server;

return call_user_func(function(){
    $app = Pie::module('Server');

    $app->listen('cleanup', function($parent) use($app){
        $this->broadcast('server', [new Server()]);
    });

    return $app;
});