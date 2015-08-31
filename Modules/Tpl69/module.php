<?php

use Object69\Core\Object69;
use Object69\Core\Scope;
use Object69\Modules\Tpl69\Tpl;
use Object69\Modules\Tpl69\TplAttr;

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

    $app->directive('controller', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, DOMElement $element, TplAttr $attr){
                $scope = $this->call($attr->value)->scope();
                if($scope instanceof Scope){
                    $attr->tpl->setScope($scope);
                }
                $element->removeAttribute('controller');
            }
        ];
    });

    $app->directive('repeat', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, DOMElement $element, TplAttr $attr){
                $repkeys = array_map('trim', explode('in', $attr->value));
                $value   = Object69::find($repkeys[1], $scope);
                $items   = new DOMDocument();
                $frag    = $items->createDocumentFragment();
                foreach($value as $item){
                    $doc = new DOMDocument();
                    $doc->appendChild($doc->importNode($element, true));
                    $tpl = new Tpl();
                    $sc  = new Scope($item, $scope);
                    $tpl->setScope($sc);
                    $tpl->setDirectives($attr->tpl->getDirectives());
                    $tpl->processNode($doc->documentElement);
                    $doc->documentElement->removeAttribute('repeat');
                    $frag->appendChild($items->importNode($doc->documentElement, true));
                }
                $element->parentNode->replaceChild($element->ownerDocument->importNode($frag, true), $element);
            }
        ];
    });

    $app->directive('scope', function(){
        return [
            'restrict' => 'AE',
            'link'     => function(Scope $scope, DOMElement $element, TplAttr $attr){
                $repeat  = $element->ownerDocument->documentElement->getAttribute('repeat');
                $content = array_map('trim', explode('|', $attr->value));
                if($repeat){
                    $repkeys = array_map('trim', explode('in', $repeat));
                    if($repkeys[0] == explode('.', $content[0])[0]){
                        $find = explode('.', $content[0]);
                        array_shift($find);
                        $find = implode('.', $find);
                    }
                }else{
                    $find = $content[0];
                }
                $value = Object69::find($find, $scope);
                $value = $attr->tpl->functions($value, $content, $scope);
                if($attr->type == 'A'){
                    $element->nodeValue = '';
                    if(strlen(strip_tags($value)) != strlen($value)){
                        $htmldoc = new DOMDocument();
                        $htmldoc->loadHTML($value, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                        $element->appendChild($element->ownerDocument->importNode($htmldoc->documentElement, true));
                    }else{
                        $element->nodeValue = $value;
                    }
                    $element->removeAttribute('scope');
                }elseif($attr->type == 'E'){
                    if(strlen(strip_tags($value)) != strlen($value)){
                        $htmldoc  = new DOMDocument();
                        $htmldoc->loadHTML($value, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                        $textNode = $element->ownerDocument->importNode($htmldoc->documentElement, true);
                    }else{
                        $textNode = $attr->doc->createTextNode($value);
                    }
                    $element->parentNode->replaceChild($textNode, $element);
                }
            }
        ];
    });

    $app->directive('include', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, DOMElement $element, TplAttr $attr){
                $doc      = new DOMDocument();
                $filename = $attr->tpl->getRealFile($attr->value);

                libxml_use_internal_errors(true);
                $doc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                libxml_use_internal_errors(false);

                $element->appendChild($attr->doc->importNode($doc->documentElement, true));
                $element->removeAttribute('include');
            }
        ];
    });

    return $app;
});
