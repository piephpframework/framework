<?php

use Pie\Crust\Pie;
use Pie\Crust\Scope;
use Pie\Modules\Tpl\RepeatInfo;
use Pie\Modules\Tpl\Tpl;
use Pie\Modules\Tpl\Element;
use Pie\Modules\Tpl\TplAttr;

return call_user_func(function(){
    $app = Pie::module('Tpl', []);

    $tpl = new Tpl();

    $app->routeChange = function ($value, $parent) use ($tpl){
        $tpl->setParent($parent);

        if(isset($value[0]['settings']['templateUrl'])){
            $filename = $tpl->getRealFile($value);

            $basefile = '';
            if(isset($value[0]['globalSettings']['baseTemplateUrl'])){
                $basefile = $tpl->getBase() . $value[0]['globalSettings']['baseTemplateUrl'];
            }
            if(isset($value[0]['settings']['baseTemplateUrl'])){
                if(!empty($value[0]['settings']['baseTemplateUrl'])){
                    $basefile = $tpl->getBase() . $value[0]['settings']['baseTemplateUrl'];
                }else{
                    $basefile = null;
                }
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

        // If no template is created don't process as a template
        if(!isset($newDoc)){
            echo '';
            return;
        }

        $directives = $parent->getDirectives();
        $tpl->setDirectives($directives);

        $dirParent = $parent;
        while($dirParent !== null){
            foreach($dirParent->getApps() as $name => $childApp){
                $tpl->addDirectives($childApp->getDirectives());
            }
            $dirParent = $dirParent->getParent();
        }

        $filters = $this->getFilters();
        $tpl->setFilters($filters);

        /* @var $child DOMElement */
        foreach($newDoc->childNodes as $child){
            $tpl->processNode($child);
        }

        $doctype = '';
        if(isset($value[0]['settings']['doctype'])){
            $doctype = $value[0]['settings']['doctype'];
            $doctype = $doctype === null ? '' : $doctype;
        }else{
            $doctype = isset($_ENV['tpl']['doctype']) ? $_ENV['tpl']['doctype'] : '<!doctype html>';
        }
        echo "$doctype\n" . $newDoc->saveHTML();
    };

    /**
     * Tells Pie the name of the controller to use for this template.
     */
    $app->directive('controller', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, Element $element, TplAttr $attr){
                $scope = $this->call($attr->value)->scope();
                if($scope instanceof Scope){
                    $attr->tpl->setScope($scope);
                }
                $element->node->removeAttribute('controller');
            }
        ];
    });

    /**
     * Loops through an an array or itteratable class.
     */
    $app->directive('repeat', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, Element $element, TplAttr $attr){
                $repkeys = array_map('trim', explode(' in ', $attr->value, 2));
                $value   = Pie::find($repkeys[1], $scope);
                $items   = new DOMDocument();
                $frag    = $items->createDocumentFragment();
                if(is_array($value) || $value instanceof Iterator){
                    $length     = $value instanceof Iterator ? $value->length : count($value);
                    $repeatInfo = new RepeatInfo($length);
                    foreach($value as $index => $item){
                        if(!is_array($item) || $item instanceof Iterator){
                            $item = [$item];
                        }
                        $doc = new DOMDocument();
                        $doc->appendChild($doc->importNode($element->node, true));
                        $tpl = new Tpl($attr->tpl->getParent());
                        $tpl->setIndex($index);
                        $tpl->setRepeat($repeatInfo);
                        $tpl->setScope(new Scope($item, $scope));
                        $tpl->setDirectives($attr->tpl->getDirectives());
                        $tpl->setFilters($attr->tpl->getFilters());
                        $tpl->processNode($doc->documentElement);
                        $doc->documentElement->removeAttribute('repeat');
                        $frag->appendChild($items->importNode($doc->documentElement, true));
                    }
                }
                $element->node->parentNode->replaceChild($element->node->ownerDocument->importNode($frag, true), $element->node);
            }
        ];
    });

    /**
     * uses text as a sperator
     */
    $app->directive('implode', function(){
        return [
            'restrict' => 'E',
            'link'     => function(Scope $scope, Element $element, TplAttr $attr){
                $repeat = $element->node->ownerDocument->documentElement->getAttribute('repeat');
                if($repeat){
                    if($attr->tpl->getIndex() + 1 != $attr->tpl->getRepeat()->length){
                        $txt = $attr->doc->createTextNode($attr->value);
                        $element->node->parentNode->replaceChild($txt, $element);
                    }else{
                        $element->node->parentNode->removeChild($element);
                    }
                }
            }
        ];
    });

    /**
     * Uses the data found in the scope to set it's value.
     * If the scope is an attribute, it will place the data as the tags text.
     * If the scope is an element, it will replace the element with the text.
     */
    $app->directive('scope', function(){
        return [
            'link'     => function(Scope $scope, Element $element, TplAttr $attr){
                $repeat  = $element->node->ownerDocument->documentElement->getAttribute('repeat');
                $content = array_map('trim', explode('|', $attr->value));
                if($repeat){
                    $find = repeatFinder($repeat, $content);
                }else{
                    $find = $content[0];
                }
                $value = Pie::find($find, $scope);
                if($value === null){
                    $cscope = $scope->getParentScope();
                    do{
                        if($cscope === null){
                            break;
                        }
                        $value = Pie::find($find, $cscope);
                        if($value !== null){
                            break;
                        }
                        $cscope = $cscope->getParentScope();
                    }while(true);
                }
                if($content[0] == '$value' && $value instanceof Scope){
                    $value = $value->get(0);
                }elseif($content[0] == '$index' && $value instanceof Scope){
                    $value = $attr->tpl->getIndex();
                }
                $value = $attr->tpl->functions($value, $content, $scope);
                if($attr->type == 'A'){
                    $element->node->nodeValue = '';
                    if(is_string($value) && strlen(strip_tags($value)) != strlen($value)){
                        $htmldoc = new DOMDocument();
                        $htmldoc->loadHTML($value, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                        $element->node->appendChild($element->node->ownerDocument->importNode($htmldoc->documentElement, true));
                    }elseif($value instanceof Scope){
                        $element->node->nodeValue = $value->get(0);
                    }else{
                        $element->node->nodeValue = $value;
                    }
                    $element->node->removeAttribute('scope');
                }elseif($attr->type == 'E'){
                    if(is_string($value) && strlen(strip_tags($value)) != strlen($value)){
                        $htmldoc  = new DOMDocument();
                        $htmldoc->loadHTML($value, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                        $textNode = $element->node->ownerDocument->importNode($htmldoc->documentElement, true);
                    }elseif($value instanceof Scope){
                        $index = $attr->tpl->getIndex();
                        if($attr->value == '$index'){
                            $textNode = $attr->doc->createTextNode($index);
                        }elseif($value instanceof Scope){
                            $textNode = $attr->doc->createTextNode($value->get(0));
                        }else{
                            $textNode = $attr->doc->createTextNode($value);
                        }
                    }else{
                        $textNode = $attr->doc->createTextNode($value);
                    }
                    $element->node->parentNode->replaceChild($textNode, $element->node);
                }
            }
        ];
    });

    /**
     * Includes a file and adds it to the dom at that location
     */
    $app->directive('include', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, Element $element, TplAttr $attr){
                $doc      = new DOMDocument();
                $filename = $attr->tpl->getRealFile($attr->value);

                libxml_use_internal_errors(true);
                $doc->loadHTMLFile($filename, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
                libxml_use_internal_errors(false);

                $element->node->appendChild($attr->doc->importNode($doc->documentElement, true));
                $element->node->removeAttribute('include');
            }
        ];
    });

    /**
     * Removes an element and its children if the condition is true
     */
    $app->directive('hide', function(){
        return [
            'restrict' => 'A',
            'link'     => function(Scope $scope, Element $element, TplAttr $attr){
                $repeat  = $element->node->ownerDocument->documentElement->getAttribute('repeat');
                $content = array_map('trim', explode('|', $attr->value));
                if($repeat){
                    $find = repeatFinder($repeat, $content);
                }else{
                    $find = $content[0];
                }
                $find = Pie::find($find, $scope);
                $result = false;
                if(!empty($find)){
                    eval('$result = (bool)(' . $find . ');');
                }
                if($result){
                    $element->node->parentNode->removeChild($element->node);
                }else{
                    $element->node->removeAttribute('hide');
                }
            }
        ];
    });

    $app->directive('show', function(){
        return [
            'restrict' => 'A',
            'link' => function(Scope $scope, Element $element, TplAttr $tplAttr){
                $show = $this->evaluate($tplAttr->value, $scope);
                if($show !== true){
                    $element->node->parentNode->removeChild($element->node);
                }else{
                    $element->node->removeAttribute($tplAttr->name);
                }
            }
        ];
    });

    foreach(glob(__DIR__ . '/filters/*.php') as $file){
        require_once $file;
    }

    function repeatFinder($repeat, $content, Tpl $tpl = null){
        $repkeys = array_map('trim', explode(' in ', $repeat));
        $find = '';
        if($repkeys[0] == explode('.', $content[0])[0]){
            $find = explode('.', $content[0]);
            array_shift($find);
            $find = implode('.', $find);
        }
        return $find;
    }

    return $app;
});
