<?php

namespace Object69\Modules\Tpl69;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Object69\App;
use Object69\Modules\Module;
use Object69\Object69;

/**
 * @property DOMDocument $doc DOM Document
 */
class Tpl69 extends Module{

    protected $scope = null;

    public function init(App $parent){
        $this->app = Object69::module('Tpl69', []);

        $this->app->routeChange = function ($value) use($parent){
            if(isset($value[0]['settings']['templateUrl'])){
                $filename = $this->getRealFile($value);

                $basefile = '';
                if(isset($value[1]['baseTemplateUrl'])){
                    $basefile = $this->getBase() . $value[1]['baseTemplateUrl'];
                }
                if(isset($value[0]['settings']['baseTemplateUrl'])){
                    $basefile = $this->getBase() . $value[0]['settings']['baseTemplateUrl'];
                }

                if(!empty($basefile)){
                    $doc = new DOMDocument();

                    libxml_use_internal_errors(true);
                    $doc->loadHTMLFile($basefile, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                    libxml_use_internal_errors(false);

                    $newDoc = $this->loadView($doc, $filename);
                }else{
                    $newDoc = new DOMDocument();

                    libxml_use_internal_errors(true);
                    $newDoc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                    libxml_use_internal_errors(false);
                }


                // Include all the files needed
                $newDoc = $this->incl($newDoc);

                $finaldoc                     = new DOMDocument();
                $finaldoc->preserveWhiteSpace = false;
                $finaldoc->formatOutput       = true;

                $finaldoc->appendChild($finaldoc->importNode($newDoc->documentElement, true));
                $xpath = new DOMXPath($finaldoc);

                /* @var $controller DOMElement */
                foreach($xpath->query('//*[@controller]') as $controller){
                    $ctrlName = $controller->getAttribute('controller');
                    $controller->removeAttribute('controller');
                    $scope    = $parent->call($ctrlName)->scope();

                    $newctrl = $this->process($controller, $scope);

                    $controller->parentNode->replaceChild($finaldoc->importNode($newctrl, true), $controller);



//                    var_dump($controller->childNodes->item(1));
                    // Test repeating items
//                    $ctrl = $this->repeat($controller, $scope);
//                    $controller->parentNode->replaceChild($doc->importNode($ctrl, true), $controller);
//
//                    // Replace other scopes
//                    $scope_nodes = $this->scope($controller, $scope);
//                    foreach($scope_nodes as $scope_node){
//                        $impNode = $doc->importNode($scope_node, true);
////                        $parent->replaceChild($impNode, $controller);
//                    }
                }
                $doctype = isset($_ENV['tpl']['doctype']) ? $_ENV['tpl']['doctype'] : "<!doctype html>";

                echo "$doctype\n" . $finaldoc->saveHTML();
            }
        };

//        $this->app->cleanup = function(){
//            echo 'here';
//        };

        return $this->app;
    }

    protected function process($controller, $scope){

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
     * @param string $filename
     * @return DOMDocument
     */
    protected function loadView(DOMDocument $doc, $filename){
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

    /**
     *
     * @param DOMDocument $doc
     * @return DOMDocument
     */
    protected function incl(DOMDocument $doc){
        $tpl = new DOMDocument();
        $tpl->appendChild($tpl->importNode($doc->documentElement, true));

        $docx = new DOMXPath($tpl);

        $root     = $this->getBase();
//        $includes = $tpl->getElementsByTagName('include');
        $includes = $docx->query('//include | //*[@include]');
        foreach($includes as $node){
//        for($i = 0; $i < $includes->length;){
//            $node    = $includes->item($i);
            $newnode = $this->braces($node, Object69::$rootScope);
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
            $value = Object69::find($scope, $name);
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
            $found = Object69::find($scope, $show);
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
            $items  = Object69::find($scope, $vals[1]);

            $doc = new DOMDocument();
            foreach($items as $item){
//                $appendedNode = $doc->appendChild($doc->importNode($node, true));

                $scopeRepl  = $this->scope($node, $item, true, $vals[0]);
                $bracesRepl = $this->braces($scopeRepl, $item, true, $vals[0]);
                $doc->appendChild($doc->importNode($bracesRepl, true));

//                $scopeNode = $appendedNode->parentNode->replaceChild($doc->importNode($bracesRepl, true), $appendedNode);
//                $scopeRepl = $this->scope($scopeNode, $item, true, $vals[0]);
//                $scopeNode->parentNode->replaceChild($doc->importNode($scopeRepl, true), $scopeNode);
//                $appendedNode->parentNode->replaceChild($doc->importNode($this->scope($appendedNode, $item, true, $vals[0]), true), $appendedNode);
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
                        $val = Object69::find($scope, $find);
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
                $val = Object69::find($scope, trim($content[0]));
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
                $scopeNode->nodeValue = $val;
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
            if(is_callable($func)){
                array_unshift($items, $value);
                $value = call_user_func_array($func, $items);
            }
        }
        return $value;
    }

    protected function getBase(){
        $base = isset($_ENV['root']['templates']) ? $_ENV['root']['templates'] : '.';
        return strpos($base, '/') === 0 ? $base : $_SERVER['DOCUMENT_ROOT'] . '/' . $base;
    }

    protected function getRealFile($value){
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

}

foreach(glob(__DIR__ . '/filters/*.php') as $file){
    require_once $file;
}