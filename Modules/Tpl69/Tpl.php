<?php

namespace Object69\Modules\Tpl69;

use DOMElement;
use DOMNode;
use Object69\Core\Scope;

class Tpl{

    protected $directives = [];
    protected $parent     = null;

    public function __construct($parent){
        $this->parent = $parent;
    }

    public function addDirective($name, $value){
        $this->directives[$name] = $value;
    }

    public function processNode(DOMElement $element){
        foreach($element->childNodes as $node){
            $this->editNode($node);
            if($node->hasChildNodes()){
                $this->processNode($node);
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
                    call_user_func($directive['link'], new Scope(), $element, $element->attributes);
                }
            }
            // Execute Element directives
            elseif(in_array('E', $restrictions) && $element instanceof DOMElement && $name == $element->tagName){
                echo 'here';
            }else{

            }
        }
    }

}
