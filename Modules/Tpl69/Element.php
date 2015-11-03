<?php

namespace Pie\Modules\Tpl69;

use DOMElement;
use DOMXPath;
use DOMNode;
use Pie\Crust\Library\Arrays\ArrayList;

class Element{

    protected $node;
    protected $text;

    public function __construct(DOMElement $element){
        $this->node = $element;
    }

    public function __get($name){
        switch($name){
            case 'text':
                return $this->node->nodeValue;
            case 'node':
                return $this->node;
        }
    }

    public function getElement(){
        return $this->element;
    }

    public function replace($value){
        $newNode = $this->newNode($value);
        $this->node = $this->element->parentNode->replaceChild($newNode, $this->element);
        return $this;
    }

    public function find($string){
        $xpath = new DOMXPath($this->element->ownerDocument);
        $nodes = new ArrayList(DOMNode::class);
        foreach($xpath->query($string) as $node){
            if($node instanceof DOMNode){
                $nodes->add(new Element($node));
            }
        }
        return $nodes;
    }

    protected function newNode($value){
        if(is_string($value)){
            return $this->element->ownerDocument->createTextNode($value);
        }
    }

}