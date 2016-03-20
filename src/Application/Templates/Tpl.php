<?php

namespace Application\Templates;

use Application\Pie;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMXPath;
use Exception;
use Application\Scope;
use Application\View;
use Application\ApplicationController;

class Tpl {

    protected $dom = null, $shellDom = null, $viewDom = null;
    protected $shell = null, $view = null;
    protected $finalView = null;
    protected $viewController = null;
    protected $scope = null;

    /**
     * Builds a template with resulting data
     * @param View $view The view to base the template on
     * @param View $shell An optional shell that will be a main wrapper for the view
     * @return View Returns a View containing the new HTML
     */
    public function getView(View $view, View $shell = null){
        // Save the view and the shell.
        // We will be using this data to create a final view.
        $this->view = $view;
        $this->shell = $shell;

        // Create a dom document.
        // Load the shell and if there isn't a shell
        // load a view in it's place.
        // Next we will replace the view tag with our
        // view if we have a shell.
        $this->dom = new DOMDocument();
        $this->shellDom = new DOMDocument();
        $this->viewDom = new DOMDocument();

        $this->loadShell();
        $this->loadView();

        // We will now replace the old view's content
        // with the new view's content and return it.
        $finalView = new View();
        $finalView->setViewTpl($this);
        return $finalView;
    }

    // public function runController(){
    //     if($this->viewController !== null){
    //         echo 'here';
    //         $this->controllerResult = $this->viewController->run();
    //     }
    // }

    public function setScope(Scope $scope = null){
        $this->scope = $scope;
    }

    public function getTemplateView(){
        return $this->view;
    }

    public function buildTemplate(){
        $this->applyRepeat($this->viewDom);
        $this->applyScope($this->scope, $this->viewDom);
        $this->createDom();
        return $this->tplFormat($this->dom->saveHTML());
    }

    /**
     * Loads the shell or view into the main $dom
     * @return void
     */
    protected function loadShell(){
        // Loads the shell into the main $dom if it exists
        // We will then use this to add our view inside of it.
        // The html within the shell must have <view></view> tags
        // they will then be replaced be the contents of the view itself.
        if($this->shell !== null && $this->shell instanceof View){
            $this->loadDom($this->shellDom, $this->shell);
        }
        // If the shell doesn't exist load the view into the main $dom
        // Since the shell doesn't exist, the view doesn't need <view></view> tags
        else{
            $this->loadDom($this->viewDom, $this->view);
        }
    }

    /**
     * Loads the view into the shell if there is a shell
     * @return void
     */
    protected function loadView(){
        if($this->shell !== null){
            $frag = $this->viewDom->createDocumentFragment();
            $this->loadDom($frag, $this->view);
            $this->viewDom->appendChild($frag);
        }
        // // If a shell has been loaded, we can attempt to load the view into it.
        // if($this->shell !== null){
        //     $frag = $this->viewDom->createDocumentFragment();
        //     $this->loadDom($frag, $this->view);
        //     // If we were able to load the view into the fragment
        //     // we then should have some nodes to replace the view or append to it.
        //     if($frag->childNodes->length > 0){
        //         $xpath = new DOMXPath($this->dom);
        //         // Find a tag with the attribute "pie-view"
        //         $nodeView = $xpath->query('//*[@pi-view]')->item(0);
        //         // If an attribute was not found, attempt to find
        //         // a tag named "view".
        //         if($nodeView === null){
        //             $nodeView = $this->shellDom->getElementsByTagName('view')->item(0);
        //         }
        //         // If we have an item from the previous two attempts
        //         // add to the tag or replace the entire tag.
        //         if($nodeView instanceof DOMElement){
        //             // If the node is a tag, replace the element
        //             if($nodeView->tagName == 'view'){
        //                 $nodeView->parentNode->replaceChild($frag, $nodeView);
        //             }
        //             // If the node is an attribute, append to the tag
        //             elseif($nodeView->hasAttribute('pi-view')){
        //                 $nodeView->appendChild($frag);
        //             }
        //             // If something unknown happens throw an exception
        //             else{
        //                 throw new Exception("Invalid type");
        //             }
        //             // Remove the attribute from the final output
        //             $nodeView->removeAttribute('pi-view');
        //         }
        //         // The node was not a valid node, throw an error
        //         else{
        //             throw new Exception("Could not load view into shell.");
        //         }
        //     }
        // }
    }

    protected function createDom(){
        // Create the document fragments to fill
        $shellFrag = $this->dom->createDocumentFragment();
        $viewFrag  = $this->dom->createDocumentFragment();
        // Import the shell and view documents into the final dom
        $shell     = $this->dom->importNode($this->shellDom->documentElement, true);
        $view      = $this->dom->importNode($this->viewDom->documentElement, true);

        // Append the shell and view to their respective fragments
        $shellFrag->appendChild($shell);
        $viewFrag->appendChild($view);
        // Append the shell to the dom
        $this->dom->appendChild($shellFrag);

        // Search for an item with a 'pi-view' attribute
        $xpath = new DOMXPath($this->dom);
        $nodeView = $xpath->query('//*[@pi-view]')->item(0);
        // If an item was not found try an find a tag called 'pi-view'
        if($nodeView === null){
            $nodeView = $this->dom->getElementsByTagName('pi-view')->item(0);
        }
        // If we found an item add the view to the final dom
        if($nodeView instanceof DOMElement){
            // If the node is a tag, replace the element
            if($nodeView->tagName == 'pi-view'){
                $nodeView->parentNode->replaceChild($viewFrag, $nodeView);
            }
            // If the node is an attribute, append to the tag
            elseif($nodeView->hasAttribute('pi-view')){
                $nodeView->appendChild($viewFrag);
            }
            // If something unknown happens throw an exception
            else{
                throw new Exception("Invalid type");
            }
            // Remove the 'pi-view' attribute from the final output
            $nodeView->removeAttribute('pi-view');
        }
    }

    protected function applyRepeat(DOMDocument $dom, $search = '', $parentKey = '', $parentVal = ''){
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//*[@pi-repeat][not(ancestor::*[@pi-repeat])]') as $node) {
            var_dump($dom->saveHTML($node));
            $value = $node->getAttribute('pi-repeat');
            $node->removeAttribute('pi-repeat');
            $value = array_map('trim', explode(' as ', $value));
            $search = empty($search) ? $value[0] : $search;
            $as = array_map('trim', explode('=>', $value[1]));
            $key = count($as) == 2 ? $as[0] : null;
            $val = count($as) == 2 ? $as[1] : $as[0];

            $scope = Pie::find($search, $this->scope);
            // var_dump($search, $scope);
            // $scope = Pie::find(preg_replace('/^'.$key.'\./', '', $search), $this->scope);

            if(is_array($scope)){
                $repeatFrag = $dom->createDocumentFragment();

                foreach($scope as $scopeKey => $scopeValue){
                    $loopSearch = $search . '.' . $scopeKey;
                    $repTpl = new DOMDocument();
                    $frag = $repTpl->createDocumentFragment();
                    $frag->appendXML($dom->saveHTML($node));
                    $repTpl->appendChild($frag);
                    $this->applyRepeat($repTpl, $loopSearch, $key, $val);
                    if($key !== null){
                        $this->repeatScope($scopeKey, $scopeValue, $key, $val, $repTpl);
                        $repeatFrag->appendXML($this->tplFormat($repTpl->saveHTML()));
                    }
                }
                $node->parentNode->replaceChild($repeatFrag, $node);
            }
        }
    }

    protected function repeatScope($scopeKey, $scopeValue, $key, $val, DOMDocument $dom){
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//*[@pi-scope]') as $node) {
            $skey = $node->getAttribute('pi-scope');
            $node->removeAttribute('pi-scope');
            if($skey == $key){
                $this->appendNode($node, $scopeKey, $dom);
            }else{
                $find = Pie::find(preg_replace('/^'.$val.'\./', '', $skey), $scopeValue);
                $this->appendNode($node, $find, $dom);
            }
        }
    }

    protected function applyScope($scope, DOMDocument $dom){
        $xpath = new DOMXPath($dom);
        // Find and loop through all 'pi-scope' attributes
        foreach ($xpath->query('//*[@pi-scope]') as $node) {
            $s = $node->getAttribute('pi-scope');
            $node->removeAttribute('pi-scope');
            // If we have a scope, place the content in the element
            // if($scope instanceof Scope){
                $value = Pie::find($s, $scope);
                $this->appendNode($node, $value, $dom);
            // }
        }
    }

    protected function closestRepeat($node){
        $parent = $node->parentNode;
        if($parent instanceof DOMNode && !$parent->getAttribute('pi-repeat')){
            return $this->closestRepeat($parent);
        }
        return null;
    }

    protected function appendNode(DOMElement $element, $value, DOMDocument $dom){
        $value = !is_string($value) ? json_encode($value) : $value;
        $txtNode = $dom->createTextNode($value);
        if(strip_tags($value) != $value){
            $d = $element->ownerDocument;
            $frag = $d->createDocumentFragment();
            $frag->appendXML($this->tplFormat($value));
            $element->appendChild($frag);
        }else{
            $element->appendChild($txtNode);
        }
    }

    /**
     * Loads html into a DOMDocument
     * @param DOMDocument $into The DOMDocument to load into
     * @param View $view The View to get the data from
     * @param string $wrapper An optional wrapper tag such as 'div'
     * @return void
     */
    protected function loadDom($into, View $view){
        libxml_use_internal_errors(true);
        $html = $view->getView();
        if($into instanceof DOMDocumentFragment){
            $into->appendXML($html);
        }else{
            if(!empty($html)){
                $into->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            }
        }
        libxml_use_internal_errors(false);
    }

    /**
     * Formats self closing tags for usage in DOMDocument
     * @param string $string The string to be formatted
     * @return string The resulting string
     */
    protected function tplFormat($string){
        $items = ['area','base','br','col','command','embed','hr','img','input','keygen','link','meta','param','source','track','wbr'];
        return preg_replace('/<(('.implode('|', $items).').*?)>/i', '<$1/>', $string);
    }

}