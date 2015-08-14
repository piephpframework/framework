<?php

namespace Modules;

use App;

/**
 * @property App $app An application instance
 */
abstract class Module{

    public
            $app = null;

    abstract public function init(App $parent);
}
