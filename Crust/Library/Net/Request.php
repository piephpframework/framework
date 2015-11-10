<?php

namespace Pie\Crust\Library\Net;

use Pie\Crust\Service;

class Request extends Service{

    /**
     * Gets the body of the request.<br>
     * If the request is in a json format then the json will be decoded
     * @return mixed
     */
    public function body(){
        $content = file_get_contents('php://input');
        $decoded = json_decode($content);
        if(json_last_error() == JSON_ERROR_NONE){
            return $decoded;
        }
        return $content;
    }

    /**
     * Gets the get query string
     * @return array
     */
    public function query(){
        return (object)$_GET;
    }

    /**
     * Gets the post data
     * @return array
     */
    public function post(){
        return (object)$_POST;
    }

    /**
     * Gets the cookie data
     * @return array
     */
    public function cookie(){
        return (object)$_COOKIE;
    }

    /**
     * Gets the session data
     * @return array
     */
    public function session(){
        return (object)$_SESSION;
    }

    /**
     * Gets the server data
     * @return array
     */
    public function server(){
        return (object)$_SERVER;
    }

    /**
     * Gets the request method
     * @return array
     */
    public function method(){
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    /**
     * Finds a value using string notation
     * @return array
     */
    public function find($path){
        return parent::_find($path);
    }

}
