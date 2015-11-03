<?php

namespace Pie\Modules\Media69;

class Image extends Media{

    protected $info = null;
    protected $fileName = null;

    public function setImage($filename){
        $this->fileName = $filename;
        $this->info = exif_read_data($this->fileName);
        return $this;
    }

    public function getFileThumb(){
        return exif_thumbnail($this->fileName);
    }

    public function getExtensions(){
        return ['tif', 'tiff', 'gif', 'jpeg', 'jpg', 'jif', 'jfif', 'jp2', 'jpx', 'j2k', 'j2c', 'fpx', 'pcd', 'png', 'pdf', 'svg'];
    }

}