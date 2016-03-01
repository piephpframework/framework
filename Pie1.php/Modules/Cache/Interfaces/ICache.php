<?php

namespace Pie\Modules\Cache\Interfaces;

interface ICache{
    public function isExpired($name, $ttl);
    public function save($name, $ttl, $callback);
    public function get($name);
    public function delete($name);
}