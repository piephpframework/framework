<?php

namespace Application;

use Application\App;

class Pie extends Object {

    public static function app($name, array $dependencies = []) {

        $app = new App($name, $dependencies);
        return $app;

    }

}