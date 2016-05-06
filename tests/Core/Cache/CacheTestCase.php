<?php
namespace Edge\Tests\Core\Cache;

use Edge\Tests\EdgeTestCase;

abstract class CacheTestCase extends EdgeTestCase{

    abstract protected function getCacheEngine();

    public function testAdd(){
        $cache = $this->getCacheEngine();
        $this->assertTrue($cache->add("key", ["value" => 2]));
    }

    public function testGet(){
        $cache = $this->getCacheEngine();
        $this->assertArrayHasKey("value", $cache->get("key"));
    }

    public function testDelete(){
        $cache = $this->getCacheEngine();
        $cache->delete("key");
        $this->assertFalse($cache->get("key"));
    }

    public function testExpiration(){
        $cache = $this->getCacheEngine();
        $this->assertTrue($cache->add('exp', 'edge', 2));
        usleep(500);
        $this->assertEquals('edge', $cache->get('exp'));
        sleep(4);
        $this->assertFalse($cache->get('exp'));
    }
}