<?php

namespace Modules\Cache;

use Modules\Cache\File;

class Cache{

    const
        File     = 'file',
        Apc      = 'apc',
        Memcache = 'memcache',
        Redis    = 'redis';

    public function get($type){
        switch($type){
            case self::File:
                return new File();
        }
    }

    public function isExpired($name, $ttl){

    }

}