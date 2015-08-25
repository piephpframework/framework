<?php

namespace Object69\Modules\Tpl69;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
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
            // Execute Attribute directives
            if(in_array('A', $restrictions) && $element instanceof DOMElement){
                $attr = $element->getAttribute($name);
                if($attr){
                    $tplAttr->type       = 'A';
                    $tplAttr->value      = $attr;
                    $tplAttr->attributes = $element->attributes;
                    call_user_func($directive['link'], $this->scope, $element, $tplAttr);
                }
            }
            // Execute Element directives
            elseif(in_array('E', $restrictions) && $element instanceof DOMElement && $name == $element->tagName){
                $tplAttr->type       = 'E';
                $tplAttr->value      = $element->nodeValue;
                $tplAttr->attributes = $element->attributes;
                call_user_func($directive['link'], $this->scope, $element, $tplAttr);
            }else{

            }
        }
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
