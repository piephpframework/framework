<?php

namespace Application\Console;

use Application\ApplicationController;

class Command {

    protected $command = '', $message = '', $controller;

    public function __get($name){
        switch($name){
            case 'command': return $this->command;
            case 'message': return $this->message;
            case 'controller': return $this->controller;
        }
    }

    /**
     * Initializes a new command
     * @param string $command The command to listen for
     * @param mixed $controller The controller for this command
     * @return Command
     */
    public function __construct($command, $message, $controller){
        $this->command = $command;
        $this->message = $message;
        $this->controller = new ApplicationController($controller);
    }

    /**
     * Runs the attached controller for this command
     */
    public function runController($args = []){
        return $this->controller->run($args);
    }

}