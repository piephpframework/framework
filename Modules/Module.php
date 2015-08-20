<?php

namespace Object69\Modules;

use Object69\App;

/**
 * @property App $app An application instance
 */
abstract class Module{

    public
        $app = null;

    abstract public function init(App $parent);
}
