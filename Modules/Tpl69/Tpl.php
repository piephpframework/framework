<?php

namespace Object69\Modules\Tpl69;

use DOMElement;
use DOMNode;
use Object69\Core\Scope;

class Tpl{

    protected $directives = [];
    protected $parent     = null;
    protected $scope      = null;

    public function __construct($parent = null){
        $this->parent = $parent;
        $this->scope  = new Scope();
    }

    public function addDirective($name, $value){
        $this->directives[$name] = $value;
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

    protected function editNode(DOMNode $element){
        foreach($this->directives as $name => $directive){
            $restrictions = str_split($directive['restrict']);
            // Execute Attribute directives
            if(in_array('A', $restrictions) && $element instanceof DOMElement){
                $attr = $element->getAttribute($name);
                if($attr){
                    call_user_func($directive['link'], $this->scope, $element, $attr);
                }
            }
            // Execute Element directives
            elseif(in_array('E', $restrictions) && $element instanceof DOMElement && $name == $element->tagName){
                $attr = $element->getAttribute($name);
                call_user_func($directive['link'], $this->scope, $element, $attr);
            }else{

            }
        }
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
