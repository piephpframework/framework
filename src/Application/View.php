<?php

namespace Application;

use Application\Templates\Tpl;
use Application\Scope;

class View {

    protected $viewName = '';
    protected $tpl = null, $content = '';
    protected $controller = null;
    protected $scope = null;

    public function __construct($view_name = '', Scope $scope = null){
        $this->viewName = $view_name;
        $this->scope = $scope;
    }

    public function setViewTpl(Tpl $tpl){
        $this->tpl = $tpl;
    }

    public function getViewTpl(){
        return $this->tpl;
    }

    public function setController($controller){
        $this->controller = $controller;
    }

    public function getScope(){
        return $this->scope;
    }

    /**
     * Get the view
     */
    public function getView(){
        if(empty($this->tpl)){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/../App/Views/' . $this->viewName . '.html';
            if(is_file($path)){
                $this->content = file_get_contents($path);
            }else{
                $this->content = '';
            }
            return $this->content;
        }else{
            return $this->tpl;
        }
    }

}