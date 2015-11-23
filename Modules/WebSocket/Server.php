<?php

namespace Pie\Modules\WebSocket;

use Closure;
use Exception;
use Pie\Crust\App;

class Server extends Socket{

    protected
        $action   = null,
        $clients  = [];

    public function start($var1, $var2 = null, $var3 = null){
        // One value passed as closure
        if($var1 instanceof Closure){
            $address = 'localhost';
            $port    = 3000;
            $this->action  = $var1;
        }
        // Value one is an address and value two is a closure
        elseif(is_string($var1) && $var2 instanceof Closure){
            $address = $var1;
            $port    = 3000;
            $this->action  = $var2;
        }
        // Value One is an address, value two is a port and value three is a closure
        elseif(is_string($var1) && ctype_digit($var2) && $var3 instanceof Closure){
            $address = $var1;
            $port    = $var2;
            $this->action  = $var3;
        }
        // Something throw an exception
        else{
            throw new Exception('Invalid start() format');
        }

        $this->action = $this->action->bindTo($this, $this);
        call_user_func_array($this->action, [$this]);

        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_strerror(socket_last_error());
        socket_bind($this->socket, $address, $port);
        socket_listen($this->socket);
        $this->running = true;
        do{
            $client = socket_accept($this->socket);
            if($client){
                $clientSocket = new ClientSocket($client);
                $this->clients[] = $clientSocket;
                $this->call('connected', [$this]);
            }
        }while($this->running);
        $this->call('close');
        socket_close($this->socket);
    }

}