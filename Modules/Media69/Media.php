<?php

namespace Pie\Modules\Media69;

use Pie\Modules\Media69\Image;
use Pie\Modules\Media69\Audio;
use Pie\Modules\Media69\Video;

class Media{

    const
        Image = 'image',
        Video = 'video',
        Audio = 'audio';

    public function getMedia($type){
        switch($type){
            case self::Image:
                return new Image();
            case self::Video:
                return new Video();
            case self::Audio:
                return new Audio();
        }
    }

}