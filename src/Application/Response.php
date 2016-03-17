<?php

namespace Application;

use SimpleXMLElement;

class Response{

    protected $code = 200;

    public function __construct($code = 200){
        $this->code($code);
    }

    public function __get($name){
        if($name == 'code'){
            return $this->code;
        }
    }

    public function code($code){
        $this->code = (int)$code;
        http_response_code($this->code);
        return $this;
    }

    public function view($name){
        return view($name);
    }

    public function json($data, $code = 200){
        $this->code($code);
        header('Content-Type: text/json');
        return json_encode($data);
    }

    public function xml($root_name, $data, $code = 200){
        $this->code($code);
        header('Content-Type: text/xml');
        // $xml = new \SimpleXMLElement('<root/>');
        // array_walk_recursive($data, array ($xml, 'addChild'));
        // $data = array('total_stud' => 500);

        // creating object of SimpleXMLElement
        $xml_data = new SimpleXMLElement('<?xml version="1.0"?><' . $root_name . '></' . $root_name . '>');

        // function call to convert array to xml
        array_to_xml($data, $xml_data);
        return $xml_data->asXML();
    }

}