<?php

namespace Object69\Modules\Session69;

use Object69\Core\Service;

class Session extends Service{

    public function __construct(){
        $use_sessions = isset($_ENV['session']['use']) ? $_ENV['session']['use'] : 'no';
        if($use_sessions == 'yes' && !headers_sent()){
            session_start();
        }
    }

    public function set($key, $value){
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null){
        if(isset($_SESSION[$key])){
            return $_SESSION[$key];
        }
        return $default;
    }

    public function delete($key){
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    public function find($path){
        return parent::_find($path, $_SESSION);
    }

}
