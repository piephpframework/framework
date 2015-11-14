<?php

namespace Pie\Modules\Media\Sources;

class Video extends Media{

    public function getExtensions(){
        return ['3g2', '3gp', 'asf', 'asx', 'avi', 'flv', 'mov', 'mp4', 'mpg', 'rm', 'swf', 'vob', 'wmv'];
    }

}