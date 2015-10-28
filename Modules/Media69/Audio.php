<?php

namespace Object69\Modules\Media69;

class Audio extends Media{

    public function getExtensions(){
        return ['aif', 'iff', 'm3u', 'm4a', 'mid', 'mp3', 'mpa', 'ra', 'wav', 'wma'];
    }

}