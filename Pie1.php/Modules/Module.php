<?php

namespace Pie\Modules;

use Pie\App;

/**
 * @property App $app An application instance
 */
abstract class Module{

    public
        $app = null;

//    abstract public function init(App $parent);
}
