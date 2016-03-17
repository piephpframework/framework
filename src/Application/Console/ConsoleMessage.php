<?php

namespace Application\Console;



class ConsoleMessage {

    private $message = '', $color = '', $fg = [];

    const
        Black       = '0;30',
        DarkGray    = '1;30',
        Blue        = '0;34',
        LightBlue   = '1;34',
        Green       = '0;32',
        LightGreen  = '1;32',
        Cyan        = '0;36',
        LightCyan   = '1;36',
        Red         = '0;31',
        LightRed    = '1;31',
        Purple      = '0;35',
        LightPurple = '1;35',
        Brown       = '0;33',
        Yellow      = '1;33',
        LightGray   = '0;37',
        White       = '1;37';

    public function __construct($message){
        $this->message = $message;
    }

    public function __get($name){
        if($name == 'message'){
            return $this->getColoredString($this->message);
        }
    }

    public function color($color){
        $this->color = $color;
        return $this;
    }

    private function getColoredString($string) {
        if(!isset($this->color) || empty($this->color) || ctype_alpha($this->color)){
            return $string;
        }
        $colored_string = "";
        // Check if given foreground color found
        $colored_string .= "\033[" . $this->color . "m";
        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";
        return $colored_string;
    }

}