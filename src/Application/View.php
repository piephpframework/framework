<?php

namespace Application;

class View {

    protected $viewName = '';

    public function __construct($view_name){
        $this->viewName = $view_name;
    }

    public function getView(){
        return file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../App/Views/' . $this->viewName . '.html');
    }

}