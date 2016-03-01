<?php

namespace Pie\Taste;

use DOMDocument;
use DOMXPath;

class Browser{

    protected static $doc;
    protected static $xpath;

    /**
     * Gets the content from a url and loads it for reading
     * @param string $url
     */
    public static function get($url){
        $content = file_get_contents($url);
        self::$doc = new DOMDocument();
        libxml_use_internal_errors(true);
        self::$doc->loadHTML($content);
        libxml_use_internal_errors(false);
        self::$xpath = new DOMXPath(self::$doc);
    }

    public static function title(){
        return self::$xpath->query('//title')->item(0)->textContent;
    }

    public static function find($query){
        return self::$xpath->query($query);
    }

}