<?php

namespace Pie\Modules\Server;

use Pie\Crust\Service;
use Pie\Crust\Net\Request;
use Pie\Crust\Net\Response;

class Server extends Service{

    protected
        $listening = false,
        $address   = 'localhost',
        $port      = 3000,
        $action    = null,
        $socket    = null;

    public function listen($port = 3000, callable $callback){
        $this->port = $port;
        $this->action = $callback->bindTo($this, $this);
        call_user_func_array($this->action, [new Request(), new Response()]);

        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_strerror(socket_last_error());
        socket_bind($this->socket, $this->address, $this->port);
        socket_listen($this->socket);

        $this->listening = true;
        do{
            $client = socket_accept($this->socket);
            if($client){
                echo 'new client';
                // $clientSocket = new ClientSocket($client);
                // $this->clients[] = $clientSocket;
            }
        }while($this->listening);
    }

}