<?php

namespace Pie\Modules\WebSocket;

class ClientSocket{

    protected $socket = null;

    public function __construct($clientSocket){
        $this->socket = $clientSocket;
    }

    public function getSocket(){
        return $this->socket;
    }

}