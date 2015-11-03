<?php

namespace Pie\Modules\Tpl;

use Closure;
use DOMDocument;
use DOMDocumentType;
use DOMElement;
use DOMNode;
use DOMXPath;
use Pie\Crust\Pie;
use Pie\Crust\Scope;
use Pie\Modules\Tpl\Element;

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
            $nodes = $element->childNodes;
            for($i = 0; $i < $nodes->length; $i++){
                $node = $nodes->item($i);
                $this->editNode($node);
                if($node->hasChildNodes()){
                    $this->processNode($node);
                }
            }
        }
    }

    public function editNode(DOMNode $node){
        $processed = 0;
        foreach($this->directives as $name => $directive){
            if($directive instanceof Closure){
                $cbParams  = $this->parent->getCallbackArgs($directive);
                $directive = call_user_func_array($directive, $cbParams);
            }
            $restrictions = isset($directive['restrict']) ? str_split($directive['restrict']) : ['A', 'E'];
            $tplAttr      = new TplAttr();
            $tplAttr->tpl = $this;
            $tplAttr->doc = $node->ownerDocument;

            // Replace the braces within attributes
            if($node instanceof DOMElement){
                $this->braces($node, $this->scope);
            }

            // Execute the Element directives
            if(in_array('E', $restrictions) && $node instanceof DOMElement && $name == $node->tagName){
                $element = $this->getElement($directive, $node);
                $scope   = $this->directiveController($directive);
                $this->directiveLink($name, $directive, $element, $node, 'E', $scope, $tplAttr, $node->nodeValue);
                $this->directiveTemplate($directive, $element, $node, $scope);
                $processed++;
            }

            // Execute the Attribute directives
            elseif(in_array('A', $restrictions) && $node instanceof DOMElement){
                if($node->hasAttribute($name)){
                    $attr    = $node->getAttribute($name);
                    $element = $this->getElement($directive, $node);
                    $scope   = $this->directiveController($directive);
                    $this->directiveLink($name, $directive, $element, $node, 'A', $scope, $tplAttr, $attr);
                    $this->directiveTemplate($directive, $element, $node, $scope);
                    $processed++;
                }
            }
        }
        return $processed;
    }

    protected function getElement($directive, DOMElement $node){
        if(isset($directive['templateUrl'])){
            return $this->loadDirectiveTemplate($directive);
        }else{
            return new Element($node);
        }
    }

    protected function directiveController($directive){
        if(isset($directive['controller']) && $directive['controller'] instanceof Closure){
            $scope  = new Scope();
            $result = $this->parent->getCallbackArgs($directive['controller'], $scope);
            call_user_func_array($directive['controller'], $result);
            return $scope;
        }elseif(!isset($directive['controller']) && empty($this->scope)){
            return Pie::$rootScope;
        }
        return $this->scope;
    }

    protected function directiveLink($name, $directive, Element $element, DOMNode $node, $type, Scope $scope, $tplAttr, $value){
        if(isset($directive['link'])){
            $tplAttr->type  = $type;
            $tplAttr->value = $value;
            $tplAttr->name  = $name;
            $tplAttr->attributes = $node->attributes;
            call_user_func_array($directive['link'], [$scope, $element, $tplAttr]);
        }
    }

    protected function directiveTemplate($directive, Element $element, DOMNode $node, Scope $scope){
        if(isset($directive['templateUrl'])){
            $tpl = new Tpl($this->parent);
            $tpl->setScope($scope);
            $tpl->setDirectives($this->directives);
            $tpl->setFilters($this->filters);
            $tpl->processNode($element->node);
            $newNode = $node->ownerDocument->importNode($element->node, true);
            $newChild = $node->parentNode->replaceChild($newNode, $node);
        }
    }

    protected function loadDirectiveTemplate($directive){
        $filename = $this->getRealFile($directive['templateUrl']);

        // Create a document to load the data into
        $doc = new DOMDocument();

        // Load the document
        libxml_use_internal_errors(true);
        $html = '<directive>' . file_get_contents($filename) . '</directive>';
        $doc->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_use_internal_errors(false);

        return new Element($doc->documentElement);
    }

    protected function braces(DOMElement $node, Scope $scope){
        $path   = $node->getNodePath();
        $xpath  = new DOMXPath($node->ownerDocument);
        $repeat = $node->ownerDocument->documentElement->getAttribute('repeat');
        if($node->hasAttribute('repeat')){
            $repeat = $node->getAttribute('repeat');
        }
        /* @var $scopeNode DOMElement */
        foreach($xpath->query($path . '[@.=(contains(., "{{") and contains(., "}}"))]') as $scopeNode){
            foreach($scopeNode->attributes as $attr){
                $matches = [];
                // var_dump($attr->value);
                if(preg_match_all('/\{\{(.+?)\}\}/', $attr->value, $matches)){
                    $attrVal = $attr->value;
                    foreach($matches[1] as $match){
                        // var_dump($match);
                        $content = array_map('trim', explode('|', $match));
                        $find    = array_shift($content);
                        if($repeat){
                            $repkeys = array_map('trim', explode('in', $repeat));
                            if(count(explode('.', $find)) == 1 && $repkeys[0] == explode('.', $find)[0]){
                                $find = $repkeys[1] . '[' . $this->getIndex() . ']';
                            }
                            if($repkeys[0] == explode('.', $find)[0]){
                                $find = explode('.', $find);
                                array_shift($find);
                                $find = implode('.', $find);
                            }
                            if($repkeys[0] == trim($attrVal, '{}')){
                                 $find = $repkeys[1] . '[' . $this->getIndex() . ']';
                            }
                        }
                        $val = Pie::find($find, $scope);
                        if(!$val){
                            $val = Pie::find($find, $scope->getParentScope());
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
                    $call = Pie::find($func, $scope);
                    if(!$call){
                        $cscope = $scope->getParentScope();
                        do{
                            if($cscope === null){
                                break;
                            }
                            $func = Pie::find($op, $cscope);
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
                    $func = Pie::find($func, Pie::$rootScope);
                }
            }
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
            // $incldoc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            $html = '<view>' . file_get_contents($filename). '</view>';
            $incldoc->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            libxml_use_internal_errors(false);

            if($incldoc->documentElement instanceof DOMNode){
                $node->appendChild($tpl->importNode($incldoc->documentElement, true));
                break;
            }
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
//        $filename = \Pie::$root . '/../' . $value[0]['settings']['templateUrl'];
//        if(is_file($filename)){
//            return $filename;
//        }
    }

    public function setParent($parent){
        $this->parent = $parent;
    }

}
