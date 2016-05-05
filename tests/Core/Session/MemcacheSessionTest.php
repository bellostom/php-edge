<?php
namespace Edge\Tests\Core\Session;

use Edge\Core\Session\SessionMemcacheStorage,
    Edge\Core\Cache\MemoryCache;

class MemcacheSessionTest extends SessionTestCase{

    protected static $dir = '/tmp/edgeCache';

    protected function getSessionEngine(){
        if (!extension_loaded("memcache")) {
            $this->markTestSkipped("memcache not installed. Skipping.");
        }
        // check whether memcached is running and skip tests if not.
        if (!@stream_socket_client('127.0.0.1:11211', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('Memcached server not running at ' . '127.0.0.1:11211' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }
        $cache = new MemoryCache([
            "namespace" => "edge",
            "servers" => ["127.0.0.1:11211:1"]
        ]);
        return new SessionMemcacheStorage($cache);
    }
}