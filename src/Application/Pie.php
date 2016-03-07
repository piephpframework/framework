<?php

namespace Application;

use Application\App;

class Pie extends Object {

    private function __construct(){}

    /**
     * Creates a new appliction
     * @param string $name The name of the application
     * @return App
     */
    public static function app($name = '', array $dependencies = []) {

        $app = new App($name, $dependencies);
        return $app;

    }

}