<?php

namespace Modules\Database69;

use App;
use Modules\Module;
use Object69;

class Database69 extends Module{

    public function init(App $parent){
        $this->app = Object69::module('Database69', []);

        $this->app->classes = [
            'db' => new Db()
        ];

        return $this->app;
    }

}
