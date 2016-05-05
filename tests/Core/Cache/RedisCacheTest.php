<?php
namespace Edge\Tests\Core\Cache;

use Edge\Core\Cache\RedisCache;

class RedisCacheTest extends CacheTestCase{

    protected function getCacheEngine(){
        static $cache = null;
        if (!extension_loaded("redis")) {
            $this->markTestSkipped("redis modile not installed. Skipping.");
        }
        if (!@stream_socket_client('127.0.0.1:6379', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('Redis server not running at ' . '127.0.0.1:6379' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }
        if(is_null($cache)){
            $cache = new RedisCache([
               "namespace" => "edge",
               "server" => "127.0.0.1:6379"
           ]);
        }
        return $cache;
    }
}