<?php
namespace Edge\Tests\Core\Cache;

use Edge\Core\Cache\MemoryCache;

class MemoryCacheTest extends CacheTestCase{

    protected function getCacheEngine(){
        static $cache = null;
        if (!extension_loaded("memcache")) {
            $this->markTestSkipped("memcache not installed. Skipping.");
        }
        // check whether memcached is running and skip tests if not.
        if (!@stream_socket_client('127.0.0.1:11211', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('Memcached server not running at ' . '127.0.0.1:11211' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }
        if(is_null($cache)){
            $cache = new MemoryCache([
                "namespace" => "edge",
                "servers" => ["127.0.0.1:11211:1"]
             ]);
        }
        return $cache;
    }
}