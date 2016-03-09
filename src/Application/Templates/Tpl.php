<?php

namespace Application\Templates;

use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMXPath;
use Exception;
use Application\View;

class Tpl {

    protected $dom = null;
    protected $shell = null, $view = null;
    protected $finalView = null;

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
        $this->loadShell();
        $this->loadView();

        // We will now replace the old view's content
        // with the new view's content and return it.
        $finalView = new View();
        $finalView->setViewContent($this->dom->saveHTML());
        return $finalView;
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
            $this->loadDom($this->dom, $this->shell);
        }
        // If the shell doesn't exist load the view into the main $dom
        // Since the shell doesn't exist, the view doesn't need <view></view> tags
        else{
            $this->loadDom($this->dom, $this->view);
        }
    }

    /**
     * Loads the view into the shell if there is a shell
     * @return void
     */
    protected function loadView(){
        // If a shell has been loaded, we can attempt to load the view into it.
        if($this->shell !== null){
            $frag = $this->dom->createDocumentFragment();
            $this->loadDom($frag, $this->view);
            // If we were able to load the view into the fragment
            // we then should have some nodes to replace the view or append to it.
            if($frag->childNodes->length > 0){
                $xpath = new DOMXPath($this->dom);
                // Find a tag with the attribute "pie-view"
                $nodeView = $xpath->query('//*[@pi-view]')->item(0);
                // If an attribute was not found, attempt to find
                // a tag named "view".
                if($nodeView === null){
                    $nodeView = $this->dom->getElementsByTagName('view')->item(0);
                }
                // If we have an item from the previous two attempts
                // add to the tag or replace the entire tag.
                if($nodeView instanceof DOMElement){
                    // If the node is a tag, replace the element
                    if($nodeView->tagName == 'view'){
                        $nodeView->parentNode->replaceChild($frag, $nodeView);
                    }
                    // If the node is an attribute, append to the tag
                    elseif($nodeView->hasAttribute('pi-view')){
                        $nodeView->appendChild($frag);
                    }
                    // If something unknown happens throw an exception
                    else{
                        throw new Exception("Invalid type");
                    }
                    // Remove the attribute from the final output
                    $nodeView->removeAttribute('pi-view');
                }
                // The node was not a valid node, throw an error
                else{
                    throw new Exception("Could not load view into shell.");
                }
            }
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

}