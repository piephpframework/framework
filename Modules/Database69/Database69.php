<?php

namespace Object69\Modules\Database69;

use Object69\App;
use Object69\Modules\Module;
use Object69\Object69;

class Database69 extends Module{

    public function init(App $parent){
        $this->app = Object69::module('Database69', []);

        $this->app->classes = [
            'db' => new Db()
        ];

        return $this->app;
    }

}
