<?php

use Object69\Core\Object69;
use Object69\Core\Scope;
use Object69\Modules\Tpl69\Tpl;

return call_user_func(function(){
    $app = Object69::module('Tpl69', []);

    $tpl = new Tpl();

    $app->routeChange = function ($value, $parent) use ($tpl){

        $tpl->setParent($parent);

        if(isset($value[0]['settings']['templateUrl'])){
            $filename = $tpl->getRealFile($value);

            $basefile = '';
            if(isset($value[1]['baseTemplateUrl'])){
                $basefile = $tpl->getBase() . $value[1]['baseTemplateUrl'];
            }
            if(isset($value[0]['settings']['baseTemplateUrl'])){
                $basefile = $tpl->getBase() . $value[0]['settings']['baseTemplateUrl'];
            }

            if(!empty($basefile)){
                $doc = new DOMDocument();

                libxml_use_internal_errors(true);
                $doc->loadHTMLFile($basefile, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                libxml_use_internal_errors(false);

                $newDoc = $tpl->loadView($doc, $filename);
            }else{
                $newDoc = new DOMDocument();

                libxml_use_internal_errors(true);
                $newDoc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                libxml_use_internal_errors(false);
            }
        }

        $directives = $this->getDirectives();
        foreach($directives as $dirName => $directive){
            $tpl->addDirective($dirName, $directive);
        }

        /* @var $child DOMElement */
        foreach($newDoc->childNodes as $child){
            $tpl->processNode($child);
        }

        $doctype = isset($_ENV['tpl']['doctype']) ? $_ENV['tpl']['doctype'] : "<!doctype html>";
        echo "$doctype\n" . $newDoc->saveHTML();

//
//
//            // Include all the files needed
//            $newDoc = $tpl->incl($newDoc);
//
//            $finaldoc                     = new DOMDocument();
//            $finaldoc->preserveWhiteSpace = false;
//            $finaldoc->formatOutput       = true;
//
//            $finaldoc->appendChild($finaldoc->importNode($newDoc->documentElement, true));
//            $xpath = new DOMXPath($finaldoc);
//
//            /* @var $controller DOMElement */
//            foreach($xpath->query('//*[@controller]') as $controller){
//                $ctrlName           = $controller->getAttribute('controller');
//                $controller->removeAttribute('controller');
//                $scope              = $parent->call($ctrlName)->scope();
//                $this->currentScope = $scope;
//
//                $newctrl = $tpl->process($controller, $scope);
//
//                $controller->parentNode->replaceChild($finaldoc->importNode($newctrl, true), $controller);
//
//
//
////                    var_dump($controller->childNodes->item(1));
//                // Test repeating items
////                    $ctrl = $this->repeat($controller, $scope);
////                    $controller->parentNode->replaceChild($doc->importNode($ctrl, true), $controller);
////
////                    // Replace other scopes
////                    $scope_nodes = $this->scope($controller, $scope);
////                    foreach($scope_nodes as $scope_node){
////                        $impNode = $doc->importNode($scope_node, true);
//////                        $parent->replaceChild($impNode, $controller);
////                    }
//            }
//            $doctype = isset($_ENV['tpl']['doctype']) ? $_ENV['tpl']['doctype'] : "<!doctype html>";
//
//            echo "$doctype\n" . $finaldoc->saveHTML();
//        }
    };

    $app->directive('controller', function() use ($tpl){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, DOMElement $element, $attr) use ($tpl){
                $scope = $this->call($attr)->scope();
                $tpl->setScope($scope);
                $tpl->processNode($element);

//                $newctrl = $tpl->process($controller, $scope);
            }
        ];
    });

    $app->directive('repeat', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, DOMElement $element, $attr){
                var_dump($scope);
            }
        ];
    });


//    $app->directive('include', function(){
//        return [
//            'restrict' => 'AE',
//            'link'     => function(Scope $scope, DOMElement $element, DOMAttr $attrs){
//                $tpl = new DOMDocument();
//                $tpl->appendChild($tpl->importNode($element, true));
//                $docx = new DOMXPath($tpl);
//
//                $root     = $this->getBase();
//                $includes = $docx->query('//include | //*[@include]');
//                foreach($includes as $node){
//                    $newnode = $this->braces($node, Object69::$rootScope);
//                    $file    = $node->getAttribute('file');
//                    $is_attr = false;
//                    if(empty($file)){
//                        $file    = $newnode->getAttribute('include');
//                        $is_attr = true;
//                    }
//
//                    $incldoc = new DOMDocument();
//
//                    libxml_use_internal_errors(true);
//                    $incldoc->loadHTMLFile($root . $file, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
//                    libxml_use_internal_errors(false);
//                    if($is_attr){
//                        $node->removeAttribute('include');
//                        $node->appendChild($tpl->importNode($incldoc->documentElement, true));
//                    }else{
//                        $node->parentNode->replaceChild($tpl->importNode($incldoc->documentElement, true), $node);
//                    }
//                }
//                return $tpl;
//            }
//        ];
//    });

    return $app;
});
