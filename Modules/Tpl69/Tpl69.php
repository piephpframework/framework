<?php

namespace Modules\Tpl69;

use App;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Modules\Module;
use Object69;

/**
 * @property DOMDocument $doc DOM Document
 */
class Tpl69 extends Module{

    protected $scope = null;

    public function init(App $parent){
        $this->app = Object69::module('Tpl69', []);

        $this->app->routeChange = function ($value) use($parent){
            if(isset($value[0]['settings']['templateUrl'])){
                $filename = \Object69::$root . $value[0]['settings']['templateUrl'];
                $basefile = '';
                if(isset($value[1]['baseTemplateUrl'])){
                    $basefile = \Object69::$root . $value[1]['baseTemplateUrl'];
                }
                if(isset($value[0]['settings']['baseTemplateUrl'])){
                    $basefile = \Object69::$root . $value[0]['settings']['baseTemplateUrl'];
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
                $xpath = new \DOMXPath($finaldoc);

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

                echo "<!doctype html>\n" . $finaldoc->saveHTML();
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
        $xpath = new \DOMXPath($tpl);

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

        $root     = Object69::$root;
        $includes = $tpl->getElementsByTagName('include');
        for($i = 0; $i < $includes->length;){
            $node = $includes->item($i);
            $file = $root . $node->getAttribute('file');

            $incldoc = new DOMDocument();

            libxml_use_internal_errors(true);
            $incldoc->loadHTMLFile($file, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            libxml_use_internal_errors(false);

            $node->parentNode->replaceChild($tpl->importNode($incldoc->documentElement, true), $node);
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

            $doc  = new DOMDocument();
            $docx = new DOMXPath($doc);
            foreach($items as $item){
                $doc->appendChild($doc->importNode($node, true));

                // find {{*}} items within attributes to be replaced
                /* @var $scopeNode DOMElement */
                foreach($docx->query('//*[*=contains(., "{{") and *=contains(., "}}")]') as $scopeNode){
                    foreach($scopeNode->attributes as $attr){
                        $matches = [];
                        if(preg_match('/\{\{(.+?)\}\}/', $attr->value, $matches)){
                            $repl = trim(preg_replace('/^' . $vals[0] . '[\.\[]/', '', $matches[1], 1), '.');
                            $val  = Object69::find($item, $repl);
                            $repl = preg_replace('/\{\{(.+?)\}\}/', $val, $attr->value);
                            $scopeNode->setAttribute($attr->name, $repl);
                        }
                    }
                }

                // find scope attributes/elements to be replaced
                /* @var $scopeNode DOMElement */
                foreach($docx->query('//scope | //*[@scope]') as $scopeNode){
                    $content = $scopeNode->getAttribute('scope');
                    $scopeNode->removeAttribute('scope');
                    $is_attr = true;
                    if(!$content){
                        $content = $scopeNode->textContent;
                        $is_attr = false;
                    }
                    $repl = trim(preg_replace('/^' . $vals[0] . '[\.\[]/', '', $content, 1), '.');
                    $val  = Object69::find($item, $repl);

                    if($is_attr){
                        $scopeNode->nodeValue = $val;
                    }else{
                        $newNode = $doc->createTextNode($val);
                        $scopeNode->parentNode->replaceChild($newNode, $scopeNode);
                    }
                }
            }

            $frag = $tpl->createDocumentFragment();
            foreach($doc->childNodes as $fragNode){
                $frag->appendChild($tpl->importNode($fragNode, true));
            }

            $node->parentNode->replaceChild($frag, $node);
        }
        return $tpl->documentElement;
    }

    protected function scope($controller, $scope){
        $tpl = new DOMDocument();
        $tpl->appendChild($tpl->importNode($controller, true));

        $docx = new \DOMXPath($tpl);
        foreach($docx->query('//scope | //*[@scope]') as $scopeNode){
            $content = $scopeNode->getAttribute('scope');
            $scopeNode->removeAttribute('scope');
            $is_attr = true;
            if(!$content){
                $content = $scopeNode->textContent;
                $is_attr = false;
            }
            $val = Object69::find($scope, $content);

            if($is_attr){
                $scopeNode->nodeValue = $val;
            }else{
                $newNode = $tpl->createTextNode($val);
                $scopeNode->parentNode->replaceChild($newNode, $scopeNode);
            }
        }
        return $tpl->documentElement;
    }

//    protected function repeat($controller, $scope){
//        $ctrlDoc = new DOMDocument();
//        $ctrlDoc->appendChild($ctrlDoc->importNode($controller, true));
//        $xpath   = new DOMXPath($ctrlDoc);
//        /* @var $node DOMNode */
//        foreach($xpath->query('//*[@repeat]') as $node){
//            $repeat = $node->getAttribute('repeat');
//
//            $doc  = new DOMDocument();
//            $frag = $doc->createDocumentFragment();
//
//            $vals = explode('in', $repeat);
//            $vals = array_map('trim', $vals);
//
//            if(is_array($scope->$vals[1])){
//                foreach($scope->$vals[1] as $index => $value){
//                    $rep_nodes = $this->scope($node, null, $index, $value, $vals);
//                    /* @var $rep_node DOMElement */
//                    foreach($rep_nodes as $rep_node){
//                        $rep_node->removeAttribute('repeat');
//                        $impNode = $doc->importNode($rep_node, true);
//                        $frag->appendChild($impNode);
//                    }
//                }
//            }
//
//            $node->parentNode->replaceChild($ctrlDoc->importNode($frag, true), $node);
//        }
//        return $ctrlDoc->firstChild;
//    }
//
//    /**
//     *
//     * @param type $doc
//     * @param type $key
//     * @param type $value
//     * @param type $placeholder
//     * @return DOMNodeList
//     */
//    protected function scope($doc, $scope = null, $key = null, $value = null, $placeholder = null){
//        $tplDoc  = new DOMDocument();
//        $tplFrag = $tplDoc->createDocumentFragment();
//        $tplFrag->appendChild($tplDoc->importNode($doc, true));
//        $tplDoc->appendChild($tplFrag);
//
//        $newDoc  = new DOMDocument();
//        $newFrag = $newDoc->createDocumentFragment();
//        /* @var $node DOMNode */
//        foreach($tplDoc->getElementsByTagName('s') as $node){
////            $attrname = $node->getAttribute('name');
//            $attrname = $node->textContent;
//            $parent   = $node->parentNode;
//            if($attrname == $placeholder[0]){
//                $txtNode = $tplDoc->createTextNode($value);
//            }else{
//                if($placeholder[0] == explode('.', $attrname)[0]){
//                    $items   = explode('.', $attrname);
//                    array_shift($items);
//                    $val     = Object69::find($value, implode('.', $items));
//                    $txtNode = $tplDoc->createTextNode($val);
//                }else{
//                    $txtNode = $tplDoc->createTextNode($scope->$attrname);
//                }
//            }
//            $node->parentNode->replaceChild($txtNode, $node);
//            $newFrag->appendChild($newDoc->importNode($parent, true));
//        }
//        if($newFrag->childNodes->length > 0){
//            $newDoc->appendChild($newFrag);
//        }
//        return $newDoc->childNodes;
//    }
}
