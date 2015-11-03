<?php

namespace Pie\Modules\Tpl69;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Pie\Crust\Pie;
use Pie\Modules\Module;

/**
 * @property DOMDocument $doc DOM Document
 */
class Tpl69 extends Module{

    protected $currentScope = null;

    public function process($controller, $scope){
        // Bind input values
        $newctrl = $this->bindInputs($controller, $scope);

        // Show elements that evaluate to true
//        $newctrl = $this->show($newctrl, $scope);
        // Loop through elements
        $newctrl = $this->repeat($newctrl, $scope);

        $newctrl = $this->scope($newctrl, $scope);

        $newctrl = $this->braces($newctrl, $scope);

        return $newctrl;
    }

    /**
     *
     * @param DOMDocument $doc
     * @return DOMDocument
     */
    public function incl(DOMDocument $doc){
        $tpl = new DOMDocument();
        $tpl->appendChild($tpl->importNode($doc->documentElement, true));

        $docx = new DOMXPath($tpl);

        $root     = $this->getBase();
//        $includes = $tpl->getElementsByTagName('include');
        $includes = $docx->query('//include | //*[@include]');
        foreach($includes as $node){
//        for($i = 0; $i < $includes->length;){
//            $node    = $includes->item($i);
            $newnode = $this->braces($node, Pie::$rootScope);
            $file    = $node->getAttribute('file');
            $is_attr = false;
            if(empty($file)){
                $file    = $newnode->getAttribute('include');
                $is_attr = true;
            }

            $incldoc = new DOMDocument();

            libxml_use_internal_errors(true);
            $incldoc->loadHTMLFile($root . $file, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            libxml_use_internal_errors(false);
            if($is_attr){
                $node->removeAttribute('include');
                $node->appendChild($tpl->importNode($incldoc->documentElement, true));
            }else{
                $node->parentNode->replaceChild($tpl->importNode($incldoc->documentElement, true), $node);
            }
        }
        return $tpl;
    }

    protected function bindInputs($controller, $scope){
        $tpl   = new DOMDocument();
        $tpl->appendChild($tpl->importNode($controller, true));
        $xpath = new DOMXPath($tpl);

        /* @var $node DOMElement */
        foreach($xpath->query('//input[@bind]') as $node){
            $name  = $node->getAttribute('bind');
            $value = Pie::find($name, $scope);
            if($value){
                $node->setAttribute('value', $value);
            }
            $node->removeAttribute('bind');
        }
        return $tpl->documentElement;
    }

    protected function show($controller, $scope){
        $tpl   = new DOMDocument();
        $tpl->appendChild($tpl->importNode($controller, true));
        $xpath = new DOMXPath($tpl);

        /* @var $node DOMElement */
        foreach($xpath->query('//*[@show]') as $node){
            $show  = $node->getAttribute('show');
            $found = Pie::find($show, $scope);
            if($found === null){
                $node->parentNode->removeChild($node);
            }
        }
        return $tpl;
    }

    protected function repeat($controller, $scope){
        $tpl   = new DOMDocument();
        $tpl->appendChild($tpl->importNode($controller, true));
        $xpath = new DOMXPath($tpl);

        $nodes = $xpath->query('//*[@repeat]');
        /* @var $node DOMElement */
        foreach($nodes as $node){
            $repeat = $node->getAttribute('repeat');
            $node->removeAttribute('repeat');
            $vals   = array_map('trim', explode('in', $repeat));
            $items  = Pie::find($vals[1], $scope);

            $doc = new DOMDocument();
            foreach($items as $item){
                $scopeRepl  = $this->scope($node, $item, true, $vals[0]);
                $bracesRepl = $this->braces($scopeRepl, $item, true, $vals[0]);
                $doc->appendChild($doc->importNode($bracesRepl, true));
            }

            $frag = $tpl->createDocumentFragment();
            foreach($doc->childNodes as $fragNode){
                $frag->appendChild($tpl->importNode($fragNode, true));
            }

            $node->parentNode->replaceChild($frag, $node);
        }
        return $tpl->documentElement;
    }

    protected function braces($controller, $scope, $repeater = false, $repeatVal = null){
        $doc  = new DOMDocument();
        $doc->appendChild($doc->importNode($controller, true));
        $docx = new DOMXPath($doc);
        // find {{*}} items within attributes to be replaced
        /* @var $scopeNode DOMElement */
        foreach($docx->query('//*[*=(contains(., "{{") and contains(., "}}"))]') as $scopeNode){
            foreach($scopeNode->attributes as $attr){
                $matches = [];
                if(preg_match('/\{\{(.+?)\}\}/', $attr->value, $matches)){
                    $content = explode('|', $matches[1]);
                    if($repeater){
                        $content[0] = trim(preg_replace('/^' . $repeatVal . '[\.\[]/', '', $content[0], 1), '.');
                    }
                    $find = trim(array_shift($content));
                    if($find == $repeatVal){
                        $val = $scope;
                    }else{
                        $val = Pie::find($find, $scope);
                    }
                    $val  = $this->functions($val, $content);
                    $repl = preg_replace('/\{\{(.+?)\}\}/', $val, $attr->value);
                    $scopeNode->setAttribute($attr->name, $repl);
                }
            }
        }
        return $doc->documentElement;
    }

    protected function scope($controller, $scope, $repeater = false, $repeatVal = null){
        $tpl = new DOMDocument();
        $tpl->appendChild($tpl->importNode($controller, true));

        $docx = new DOMXPath($tpl);
        foreach($docx->query('//scope | //*[@scope]') as $scopeNode){
            $content = explode('|', $scopeNode->getAttribute('scope'));
            /* @var $scopeNode DOMElement */
            $scopeNode->removeAttribute('scope');
            $is_attr = true;
            if(!$content[0] || empty($content[0])){
                $content = explode('|', $scopeNode->textContent);
                $is_attr = false;
            }
            if($repeater){
                $content[0] = trim(preg_replace('/^' . $repeatVal . '[\.\[]/', '', $content[0], 1), '.');
            }
            if($content[0] == $repeatVal){
                $val = $scope;
            }else{
                $val = Pie::find(trim($content[0]), $scope);
            }
            if(is_array($val)){
                $val = json_encode($val);
            }elseif(is_callable($val)){
                $val = call_user_func_array($val, []);
            }

            if(count($content) > 1){
                array_shift($content);
                $val = $this->functions($val, $content);
            }

            if($is_attr){
                if(strlen(strip_tags($val)) != strlen($val)){
                    $scopeNode->nodeValue = '';
                    $scopeHtml            = new DOMDocument();
                    $scopeHtml->loadHTML($val, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                    $scopeNode->appendChild($tpl->importNode($scopeHtml->documentElement, true));
                }else{
                    $scopeNode->nodeValue = $val;
                }
            }else{
                $newNode = $tpl->createTextNode($val);
                $scopeNode->parentNode->replaceChild($newNode, $scopeNode);
            }
        }
        return $tpl->documentElement;
    }

    protected function functions($value, $operations){
        $operations = array_map('trim', $operations);
        foreach($operations as $op){
            $items = explode(":", $op);
            $func  = array_shift($items);
            array_unshift($items, $value);
            if(!is_callable($func)){
                $call = Pie::find($func, $this->currentScope);
                if(!$call){
                    $func = Pie::find($func, Pie::$rootScope);
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

}
