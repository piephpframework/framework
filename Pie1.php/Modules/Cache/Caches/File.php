<?php

namespace Pie\Modules\Cache\Caches;

use Pie\Modules\Cache\Cache;
use Pie\Modules\Cache\Interfaces\ICache;

class File extends Cache implements ICache{

    private $filepath;

    public function __construct(){
        $this->filepath = Pie::find('$env.cache.fileroot');
    }

    public function isExpired($name, $ttl){
        $file = $this->cachePath($name);
        if(is_file($file)){
            if($ttl === null){
                return false;
            }
            $mtime = filemtime($file);
            return (time() >= ($mtime + $ttl));
        }
        return true;
    }

    public function save($name, $ttl, $callback){
        if($callback instanceof Closure){
            $result = call_user_func($callback);
            $content = json_encode($result);
            $file = $this->cachePath($name);
            file_put_contents($file, $content);
            return true;
        }
        return false;
    }

    public function get($name){
        $file = $this->cachePath($name);
        if(is_file($file)){
            $content = file_get_contents($file);
            return json_decode($content);
        }
        return '';
    }

    public function delete($name){
        $file = $this->cachePath($name);
        if(is_file($file)){
            return unlink($file);
        }
        return false;
    }

    protected function cachePath($name){
        return $this->filepath . '/' . $name . '.cache.json';
    }

}