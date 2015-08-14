<?php

namespace Modules\Tpl69;

use App;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
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

        $this->app->routeChange = function ($value){
            if(isset($value['settings']['templateUrl'])){
                $file = $value['settings']['templateUrl'];

                $doc = new DOMDocument();
                $doc->loadXML(file_get_contents($file));

                $ctrlName = null;
                /* @var $controller DOMElement */
                foreach($doc->getElementsByTagName('controller') as $controller){
                    $ctrlName = $controller->getAttribute('name');
                    $scope    = $this->app->call($ctrlName)->scope();

                    // Test repeating items
                    $ctrl = $this->repeat($controller, $scope);
                    $controller->parentNode->replaceChild($doc->importNode($ctrl, true), $controller);

                    // Replace other scopes
                    $scope_nodes = $this->scope($controller, $scope);
                    foreach($scope_nodes as $scope_node){
                        $impNode = $doc->importNode($scope_node, true);
//                        $parent->replaceChild($impNode, $controller);
                    }
                }

                echo $doc->saveHTML();
            }
        };

//        $this->app->cleanup = function(){
//            echo 'here';
//        };

        return $this->app;
    }

    protected function repeat($controller, $scope){
        $ctrlDoc = new DOMDocument();
        $ctrlDoc->appendChild($ctrlDoc->importNode($controller, true));
        $xpath   = new DOMXPath($ctrlDoc);
        /* @var $node DOMNode */
        foreach($xpath->query('//*[@repeat]') as $node){
            $repeat = $node->getAttribute('repeat');

            $doc  = new DOMDocument();
            $frag = $doc->createDocumentFragment();

            $vals = explode("in", $repeat);
            $vals = array_map('trim', $vals);

            if(is_array($scope->$vals[1])){
                foreach($scope->$vals[1] as $index => $value){
                    $rep_nodes = $this->scope($node, null, $index, $value, $vals[0]);
                    /* @var $rep_node DOMElement */
                    foreach($rep_nodes as $rep_node){
                        $rep_node->removeAttribute('repeat');
                        $impNode = $doc->importNode($rep_node, true);
                        $frag->appendChild($impNode);
                    }
                }
            }

            $node->parentNode->replaceChild($ctrlDoc->importNode($frag, true), $node);
        }
        return $ctrlDoc->firstChild;
    }

    /**
     *
     * @param type $doc
     * @param type $key
     * @param type $value
     * @param type $placeholder
     * @return DOMNodeList
     */
    protected function scope($doc, $scope = null, $key = null, $value = null, $placeholder = null){
        $tplDoc  = new DOMDocument();
        $tplFrag = $tplDoc->createDocumentFragment();
        $tplFrag->appendChild($tplDoc->importNode($doc, true));
        $tplDoc->appendChild($tplFrag);

        $newDoc  = new DOMDocument();
        $newFrag = $newDoc->createDocumentFragment();
        /* @var $node DOMNode */
        foreach($tplDoc->getElementsByTagName('scope') as $node){
            $attrname = $node->getAttribute('name');
            $parent   = $node->parentNode;
            if($attrname == $placeholder){
                $txtNode = $tplDoc->createTextNode($value);
            }else{
                var_dump($attrname);
                $txtNode = $tplDoc->createTextNode($scope->$attrname);
            }
            $node->parentNode->replaceChild($txtNode, $node);
            $newFrag->appendChild($newDoc->importNode($parent, true));
        }
        if($newFrag->childNodes->length > 0){
            $newDoc->appendChild($newFrag);
        }
        return $newDoc->childNodes;
    }

}
