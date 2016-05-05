<?php
namespace Edge\Tests\Core\Cache;

use Edge\Core\Cache\FileCache;

class FileCacheTest extends CacheTestCase{

    protected static $dir = '/tmp/edgeCache';

    protected function getCacheEngine(){
        static $cache = null;
        if(is_null($cache)){
            $cache = new FileCache(static::$dir, "edge");
        }
        return $cache;
    }

    public static function tearDownAfterClass(){
        exec("rm -rf ". static::$dir);
    }
}