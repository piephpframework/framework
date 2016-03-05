<?php

use Application\View;

/**
 * A short hand way to create a new view from any controller
 * @param string $name The name of the view
 * @return View Returns a new instance of a View
 */
function view($name){
    return new View($name);
}

function response(){

}