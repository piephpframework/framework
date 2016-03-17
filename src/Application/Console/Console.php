<?php

namespace Application\Console;

use Collections\ArrayList;

class Console {

    protected $commands, $listen = true;

    public function __construct(){
        $this->commands = new ArrayList(Command::class);
    }

    public function listen(callable $callback = null, $input_prefix = ''){
        if($callback !== null && is_callable($callback)){
            $callbackResult = call_user_func($callback, $this);
            if($callbackResult instanceof ConsoleMessage){
                echo $callbackResult->message;
            }
        }
        $handle = fopen ("php://stdin","r");
        while($this->listen){
            echo $input_prefix;
            $command = trim(fgets($handle));
            foreach($this->commands as $cmd){
                $commands = array_map('trim', explode('|', $cmd->command));
                if(in_array($command, $commands)){
                    $result = $cmd->runController([$this]);
                    if($result instanceof ConsoleMessage){
                        echo $result->message;
                    }
                }
            }
        }
    }

    public function run(callable $callback){
        call_user_func_array($callback, [$this]);
        foreach($this->commands as $cmd){
            $handle = fopen ("php://stdin","r");
            echo $cmd->message . "\n";
            $input = trim(fgets($handle));
            $result = $cmd->runController([$input]);
            if(is_string($result)){
                echo $result;
            }
        }
    }

    public function quit(){
        $this->listen = false;
    }

    public function input($command, callable $controller){
        $cmd = new Command($command, '', $controller);
        $this->commands->add($cmd);
        return $this;
    }

    public function ask($message, callable $controller){
        $cmd = new Command('', $message, $controller);
        $this->commands->add($cmd);
        return $this;
    }

}