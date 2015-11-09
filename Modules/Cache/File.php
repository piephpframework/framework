<?php

namespace Modules\Cache;

class File extends Cache{

    private $filepath = '.';

    public function __construct(){
        $this->filepath = Pie::find('$env.cache.filepath');
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
        }
    }

    public function get($name){
        $file = $this->cachePath($name);
        if(is_file($file)){
            $content = file_get_contents($file);
            return json_decode();
        }
        return '';
    }

    public function delete($name){
        $file = $this->cachePath($name);
        if(is_file($file)){
            unlink($file);
        }
    }

    protected function cachePath($name){
        return $this->filepath . '/' . $name . '.cache.json';
    }

}