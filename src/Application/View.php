<?php

namespace Application;

class View {

    protected $viewName = '';
    protected $content = '';

    public function __construct($view_name = ''){
        $this->viewName = $view_name;
    }

    public function setViewContent($content){
        $this->content = $content;
    }

    /**
     * Get the view
     */
    public function getView(){
        if(empty($this->content)){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/../App/Views/' . $this->viewName . '.html';
            if(is_file($path)){
                $this->content = file_get_contents($path);
            }else{
                $this->content = '';
            }
        }
        return tplFormat($this->content);
    }

    /**
     * Formats the content for usage in Templating
     * @param string $string The string to be formatted
     * @return string The resulting string
     */
    public function tplFormat($string){
        return str_replace(['<br>','<hr>'], ['<br/>', '<hr/>'], $string);
    }

}