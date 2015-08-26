<?php

namespace Object69\Modules\Tpl69;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Object69\Core\Object69;
use Object69\Core\Scope;

class Tpl{

    protected $directives = [];
    protected $parent     = null;
    protected $scope      = null;
    protected $repeat     = null;

    public function __construct($parent = null){
        $this->parent = $parent;
        $this->scope  = new Scope();
    }

    public function addDirective($name, $value){
        $this->directives[$name] = $value;
    }

    public function setDirectives($directives){
        $this->directives = $directives;
    }

    public function getDirectives(){
        return $this->directives;
    }

    public function setRepeat($value){
        $this->repeat = $value;
    }

    public function getRepeat(){
        return $this->repeat;
    }

    public function setScope(Scope $scope){
        $this->scope = $scope;
    }

    public function processNode(DOMNode $element){
        if($element instanceof DOMElement){
            foreach($element->childNodes as $node){
                $this->editNode($node);
                if($node->hasChildNodes()){
                    $this->processNode($node);
                }
            }
        }
    }

    public function editNode(DOMNode $element){
        foreach($this->directives as $name => $directive){
            $restrictions = str_split($directive['restrict']);
            $tplAttr      = new TplAttr();
            $tplAttr->tpl = $this;
            $tplAttr->doc = $element->ownerDocument;
            // Replace the braces within attributes
            if($element instanceof DOMElement){
                $this->braces($element, $this->scope);
            }
            // Execute Element directives
            if(in_array('E', $restrictions) && $element instanceof DOMElement && $name == $element->tagName){
                $tplAttr->type       = 'E';
                $tplAttr->value      = $element->nodeValue;
                $tplAttr->attributes = $element->attributes;
                call_user_func($directive['link'], $this->scope, $element, $tplAttr);
            }
            // Execute Attribute directives
            elseif(in_array('A', $restrictions) && $element instanceof DOMElement){
                $attr = $element->getAttribute($name);
                if($attr){
                    $tplAttr->type       = 'A';
                    $tplAttr->value      = $attr;
                    $tplAttr->attributes = $element->attributes;
                    call_user_func($directive['link'], $this->scope, $element, $tplAttr);
                }
            }else{

            }
        }
    }

    protected function braces(DOMElement $node, $scope){
        $path  = $node->getNodePath();
        $xpath = new \DOMXPath($node->ownerDocument);
        /* @var $scopeNode DOMElement */
        foreach($xpath->query($path . '[*=(contains(., "{{") and contains(., "}}"))]') as $scopeNode){
            foreach($scopeNode->attributes as $attr){
                $matches = [];
                if(preg_match('/\{\{(.+?)\}\}/', $attr->value, $matches)){
                    $content = array_map('trim', explode('|', $matches[1]));
                    $find    = array_shift($content);
                    $val     = Object69::find($scope, $find);
                    $val     = $this->functions($val, $content, $scope);
                    $repl    = preg_replace('/\{\{(.+?)\}\}/', $val, $attr->value);
                    $scopeNode->setAttribute($attr->name, $repl);
//                    var_dump($doc->documentElement);
//                    $node->parentNode->replaceChild($node->ownerDocument->importNode($doc->documentElement, true), $node);
                }
            }
        }
    }

//    protected function braces($node, $scope, $repeater = false, $repeatVal = null){
//        $doc  = new DOMDocument();
//        $doc->appendChild($doc->importNode($node, true));
//        $docx = new DOMXPath($doc);
//        // find {{*}} items within attributes to be replaced
//        /* @var $scopeNode DOMElement */
//        foreach($docx->query('//*[*=(contains(., "{{") and contains(., "}}"))]') as $scopeNode){
//            foreach($scopeNode->attributes as $attr){
//                $matches = [];
//                if(preg_match('/\{\{(.+?)\}\}/', $attr->value, $matches)){
//                    $content = explode('|', $matches[1]);
//                    if($repeater){
//                        $content[0] = trim(preg_replace('/^' . $repeatVal . '[\.\[]/', '', $content[0], 1), '.');
//                    }
//                    $find = trim(array_shift($content));
//                    if($find == $repeatVal){
//                        $val = $scope;
//                    }else{
//                        $val = Object69::find($scope, $find);
//                    }
//                    $val  = $this->functions($val, $content, $scope);
//                    $repl = preg_replace('/\{\{(.+?)\}\}/', $val, $attr->value);
//                    $scopeNode->setAttribute($attr->name, $repl);
//                }
//            }
//        }
//        return $doc->documentElement;
//    }

    protected function functions($value, $operations, $scope){
        $operations = array_map('trim', $operations);
        foreach($operations as $op){
            $items = explode(":", $op);
            $func  = array_shift($items);
            array_unshift($items, $value);
            if(!is_callable($func)){
                $call = Object69::find($scope, $func);
                if(!$call){
                    $func = Object69::find(Object69::$rootScope, $func);
                }else{
                    $func = $call;
                }
            }
            if(is_callable($func)){
                $value = call_user_func_array($func, $items);
            }
        }
        return $value;
    }

    /**
     *
     * @param DOMDocument $doc
     * @param string $filename
     * @return DOMDocument
     */
    public function loadView(DOMDocument $doc, $filename){
        $tpl   = new DOMDocument();
        $tpl->appendChild($tpl->importNode($doc->documentElement, true));
        $xpath = new DOMXPath($tpl);

        /* @var $node DOMElement */
        foreach($xpath->query('//*[@view]') as $node){
            $node->removeAttribute('view');
            $incldoc = new DOMDocument();
            libxml_use_internal_errors(true);
            $incldoc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            libxml_use_internal_errors(false);

            $node->appendChild($tpl->importNode($incldoc->documentElement, true));
            break;
        }
        return $tpl;
    }

    public function getBase(){
        $base = isset($_ENV['root']['templates']) ? $_ENV['root']['templates'] : '.';
        return strpos($base, '/') === 0 ? $base : $_SERVER['DOCUMENT_ROOT'] . '/' . $base;
    }

    public function getRealFile($value){
        $root     = $this->getBase();
        $filename = $root . $value[0]['settings']['templateUrl'];
        if(is_file($filename)){
            return $filename;
        }
//        $filename = \Object69::$root . '/../' . $value[0]['settings']['templateUrl'];
//        if(is_file($filename)){
//            return $filename;
//        }
    }

    public function setParent($parent){
        $this->parent = $parent;
    }

}
