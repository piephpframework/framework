<?php

namespace Pie\Modules\Media;

use Pie\Modules\Media\Image;
use Pie\Modules\Media\Audio;
use Pie\Modules\Media\Video;

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