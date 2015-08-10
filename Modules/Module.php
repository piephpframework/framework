<?php

namespace Modules;

abstract class Module{

    public
            $app = null;

    abstract public function init();
}
