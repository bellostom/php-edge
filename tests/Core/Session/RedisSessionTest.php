<?php
namespace Edge\Tests\Core\Session;

use Edge\Core\Session\SessionRedisStorage,
    Edge\Core\Cache\RedisCache;

class RedisSessionTest extends SessionTestCase{

    protected function getSessionEngine(){
        if (!extension_loaded("redis")) {
            $this->markTestSkipped("redis modile not installed. Skipping.");
        }
        if (!@stream_socket_client('127.0.0.1:6379', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('Redis server not running at ' . '127.0.0.1:6379' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }
        $cache = new RedisCache([
            "namespace" => "edge",
            "server" => "127.0.0.1:6379"
        ]);
        return new SessionRedisStorage($cache);
    }
}