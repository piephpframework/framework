<?php

use Pie\Pie;
use Pie\Modules\WebSocket\Server;
use Pie\Modules\WebSocket\Client;

return call_user_func(function(){
    $app = Pie::module('WebSocket');

    $server = new Server($app);
    $client = new Client($app);

    $app->service('server', $server);
    $app->service('client', $client);

    // Starts the Web Socket Server
    $app->listen('WsStart', function($address = 'localhost', $port = 3030) use ($server){
        $server->start($address, $port);
    });

    // Stops the Web Socket Server
    $app->listen('WsStop', function() use ($server){
        $server->stop();
    });

    return $app;
});