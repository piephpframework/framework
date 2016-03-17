<?php

use Application\View;
use Application\Response;
use Application\Console\ConsoleMessage;

/**
 * A short hand way to create a new view from any controller
 * @param string $name The name of the view
 * @return View Returns a new instance of a View
 */
function view($name){
    return new View($name);
}

function response($code = 200){
    $sapi = php_sapi_name();
    if($sapi === 'cli'){
        return new ConsoleMessage(trim($code) . "\n");
    }
    return new Response($code);
}

if (!function_exists('apache_response_headers')) {
    function apache_response_headers () {
        $arh = [];
        $headers = headers_list();
        foreach ($headers as $header) {
            $header = explode(":", $header);
            $arh[array_shift($header)] = trim(implode(":", $header));
        }
        return $arh;
    }
}

function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
    }
}