<?php

namespace Pie\Modules\Cache;

use Pie\Modules\Cache\Caches\File;

class Cache{

    const
        File     = 'file',
        Apc      = 'apc',
        Memcache = 'memcache',
        Redis    = 'redis';

    protected $cache;

    public function __construct($type = self::File){
        $this->get($type);
    }

    public function get($type = self::File){
        switch($type){
            case self::File:
                $this->cache = new File();
                break;
        }
        return $this;
    }

    public function isExpired($name, $ttl){
        return $this->cache->isExpired($name, $ttl);
    }

    public function save($name, $ttl, $callback){
        return $this->cache->save($name, $ttl, $callback);
    }

    public function get($name){
        return $this->cache->get($name);
    }

    public function delete($name){
        return $this->cache->delete($name);
    }

}