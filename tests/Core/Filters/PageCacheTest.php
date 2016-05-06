<?php
namespace Edge\Tests\Core\Filters;

use Edge\Tests\EdgeWebTestCase,
    Edge\Core\Edge,
    Edge\Core\Filters\PageCache;

class PageCacheTest extends EdgeWebTestCase{

    protected $filter;

    public function setUp(){
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/method';
        $this->filter = new PageCache(array(
                          'varyBy' => 'url',
                          'ttl' => 0,
                          'cacheValidator' => null,
                          'key' => "page_cache"
                      ));
    }

    public function tearDown(){
        Edge::app()->cache->delete("page_cache");
        parent::tearDown();
    }

    public function testPreProcessWithNoCachedVersion(){
        $this->assertTrue($this->filter->preProcess(Edge::app()->response, Edge::app()->request));
    }

    public function testPreProcessWithCachedVersion(){
        Edge::app()->cache->add("page_cache", "Cached version");
        $this->assertFalse($this->filter->preProcess(Edge::app()->response, Edge::app()->request));
        $this->assertEquals("Cached version", Edge::app()->response->body);
    }

    public function testPostProcessWithNoCachedVersion(){
        Edge::app()->response->body = "Cached version";
        $this->assertTrue($this->filter->postProcess(Edge::app()->response, Edge::app()->request));
        $this->assertEquals("Cached version", Edge::app()->cache->get("page_cache"));
    }

}