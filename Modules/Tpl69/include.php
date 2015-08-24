<?php

use Object69\Core\Object69;
use Object69\Core\Scope;

call_user_func(function (){
    $app = Object69::module('include', []);

    $app->directive('include', function(){
        return [
            'restrict' => 'AE',
            'link'     => function(Scope $scope, DOMElement $element, DOMAttr $attr){

            }
        ];
    });
});
