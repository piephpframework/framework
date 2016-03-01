<?php

namespace Pie\Crust\Net;

use Pie\Crust\Service;

class Response extends Service{

    public function json($data){
        return json_encode($data);
    }

}