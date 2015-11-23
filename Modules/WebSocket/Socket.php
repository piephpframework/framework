<?php

namespace Pie\Modules\WebSocket;

use Closure;
use Pie\Crust\Service;
use Pie\Crust\App;

class Socket extends Service{

    protected
        $app      = null,
        $socket   = null,
        $running  = false,
        $messages = [];

    public function __construct(App $app){
        $this->app = $app;
    }

    public function __destruct(){
        if($this->socket !== null){
            $this->call('close');
            socket_close($this->socket);
        }
    }

    public function on($name, callable $action){
        if($action instanceof Closure){
            $this->messages[$name] = $action;
        }
    }

    protected function call($name, array $params = []){
        if(isset($this->messages[$name])){
            call_user_func_array($this->messages[$name], $params);
        }
    }

    public function emit($name, $message){
        $this->write($this->socket, $message);
    }

    public function broadcast($name, $message){
        foreach($this->clients as $client){
            $this->write($client->getSocket(), $message);
        }
    }

    private function write($socket, $message){
        socket_write($socket, json_encode($message));
    }

    private function read($socket){
        return json_decode(socket_read($socket, 1024));
    }

    public function stop(){
        $this->running = false;
    }

}