<?php

namespace Pie\Modules\WebSocket;

use Closure;
use Exception;

class Client extends Socket{

    protected
        $action = null,
        $messages = [];

    public function connect($var1, $var2 = null, $var3 = null){
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

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $address, $port);
        do{
            $buff = socket_read($this->socket, 1024);
            if($buff !== false){
                var_dump($buff);
                break;
            }
        }while(true);
        call_user_func_array($this->action, [$this]);

        // $this->running = true;
        // do{
        //     $buf = socket_read($this->socket, 1024);
        //     if($buf !== false){
        //         var_dump($buf);
        //         // $this->call();
        //     }
        // }while($this->running);
        // $this->call('close');
        // socket_close($this->socket);
    }

    public function emit($name, $message){
        socket_write($this->socket, $message);
    }

}