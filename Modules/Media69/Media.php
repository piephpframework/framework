<?php

namespace Object69\Modules\Media69;

use Object69\Modules\Media69\Image;
use Object69\Modules\Media69\Audio;
use Object69\Modules\Media69\Video;

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