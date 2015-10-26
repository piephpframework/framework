<?php

namespace Object69\Modules\Tpl69;

use Closure;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Object69\Core\Object69;
use Object69\Core\Scope;

class Tpl{

    protected $directives = [], $filters    = [];
    protected $parent     = null;
    protected $scope      = null;
    protected $repeat     = null;
    protected $index      = null;

    public function __construct($parent = null){
        $this->parent = $parent;
        $this->scope  = new Scope();
    }

    public function addDirective($name, $value){
        $this->directives[$name] = $value;
    }

    public function addDirectives($directives){
        foreach($directives as $name => $value){
            $this->directives[$name] = $value;
        }
    }

    public function setDirectives($directives){
        $this->directives = $directives;
    }

    public function setFilters($filters){
        $this->filters = $filters;
    }

    public function getDirectives(){
        return $this->directives;
    }

    public function getFilters(){
        return $this->filters;
    }

    public function getParent(){
        return $this->parent;
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

    public function setIndex($index){
        $this->index = $index;
    }

    public function getIndex(){
        return $this->index;
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

    public function editNode(DOMNode $node){
        foreach($this->directives as $name => $directive){
            $restrictions = str_split($directive['restrict']);
            $tplAttr      = new TplAttr();
            $tplAttr->tpl = $this;
            $tplAttr->doc = $node->ownerDocument;
            // Replace the braces within attributes
            if($node instanceof DOMElement){
                $this->braces($node, $this->scope);
            }
            // Execute Element directives
            if(in_array('E', $restrictions) && $node instanceof DOMElement && $name == $node->tagName){
                if(isset($directive['templateUrl'])){
                    $element = $this->loadDirectiveTemplate($directive);
                }else{
                    $element = new Element($node);
                }
                $tplAttr->type       = 'E';
                $tplAttr->value      = $node->nodeValue;
                $tplAttr->attributes = $node->attributes;
                call_user_func_array($directive['link'], [$this->scope, $element, $tplAttr]);
                if(isset($directive['templateUrl'])){
                    $tpl = new Tpl($this->parent);
                    $tpl->setScope($this->scope);
                    $tpl->setDirectives($this->directives);
                    foreach($element->getElement()->childNodes as $child){
                        $tpl->processNode($child);
                    }
                    $newNode = $node->ownerDocument->importNode($element->getElement(), true);
                    $node->parentNode->replaceChild($newNode, $node);
                }
            }
            // Execute Attribute directives
            elseif(in_array('A', $restrictions) && $node instanceof DOMElement){
                $attr = $node->getAttribute($name);
                if($attr){
                    $tplAttr->type       = 'A';
                    $tplAttr->value      = $attr;
                    $tplAttr->attributes = $node->attributes;
                    $element = new Element($node);
                    call_user_func_array($directive['link'], [$this->scope, $element, $tplAttr]);
                }
            }else{

            }
        }
    }

    protected function loadDirectiveTemplate($directive){
        $filename = $this->getRealFile($directive['templateUrl']);
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_use_internal_errors(false);
        $tempNode = $doc->documentElement;
        $element = new Element($tempNode);
        return $element;
    }

    protected function braces(DOMElement $node, Scope $scope){
        $path   = $node->getNodePath();
        $xpath  = new \DOMXPath($node->ownerDocument);
        $repeat = $node->ownerDocument->documentElement->getAttribute('repeat');
        /* @var $scopeNode DOMElement */
        foreach($xpath->query($path . '[*=(contains(., "{{") and contains(., "}}"))]') as $scopeNode){
            foreach($scopeNode->attributes as $attr){
                $matches = [];
                if(preg_match_all('/\{\{(.+?)\}\}/', $attr->value, $matches)){
                    $attrVal = $attr->value;
                    foreach($matches[1] as $match){
                        $content = array_map('trim', explode('|', $match));
                        $find    = array_shift($content);
                        if($repeat){
                            $repkeys = array_map('trim', explode('in', $repeat));
                            if(count(explode('.', $find)) == 1 && $repkeys[0] == explode('.', $find)[0]){
                                $find = $repkeys[1] . '[' . $this->getIndex() . ']';
                            }if($repkeys[0] == explode('.', $find)[0]){
                                $find = explode('.', $find);
                                array_shift($find);
                                $find = implode('.', $find);
                            }
                        }
                        $val = Object69::find($find, $scope);
                        if(!$val){
                            $val = Object69::find($find, $scope->getParentScope());
                        }
                        $val = $this->functions($val, $content, $scope);
                        if(is_string($val)){
                            $attrVal = preg_replace('/\{\{(.+?)\}\}/', $val, $attrVal, 1);
                        }
                    }
                    $scopeNode->setAttribute($attr->name, $attrVal);
                }
            }
        }
    }

    public function functions($value, $operations, Scope $scope){
        $operations = array_map('trim', $operations);
        foreach($operations as $index => $op){
            $items  = explode(":", $op);
            $func   = array_shift($items);
            array_unshift($items, $value);
            $filter = $this->findFilter($func);
            if($filter){
                $func = $filter;
            }else{
                if(!is_callable($func)){
                    $call = Object69::find($func, $scope);
                    if(!$call){
                        $cscope = $scope->getParentScope();
                        do{
                            if($cscope === null){
                                break;
                            }
                            $func = Object69::find($op, $cscope);
                            if($func !== null){
                                break;
                            }
                            $cscope = $cscope->getParentScope();
                        }while(true);
                    }else{
                        $func = $call;
                    }
                }
                if(!$call){
                    $func = Object69::find($func, Object69::$rootScope);
                }
            }
//            if(is_callable($func)){
            if($func instanceof Closure){
                $value = call_user_func_array($func, $items);
            }
        }
        return $value;
    }

    protected function findFilter($filterName, $parent = null){
        $current = $parent === null ? $this : $parent;
        $filters = $current->getFilters();
        foreach($filters as $name => $filter){
            if($name == $filterName){
                return $filter;
            }
        }
        if($current->getParent() !== null){
            return $this->findFilter($filterName, $current->parent);
        }
        return null;
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

        if(!is_file($filename)){
            return $tpl;
        }

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
        $root = $this->getBase();
        if(is_array($value)){
            $filename = $root . $value[0]['settings']['templateUrl'];
        }elseif(is_string($value)){
            $filename = $root . $value;
        }
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
