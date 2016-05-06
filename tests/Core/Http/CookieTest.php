<?php
namespace Edge\Tests\Core\Http;

use Edge\Tests\EdgeTestCase,
    Edge\Core\Http\Cookie;

class CookieTest extends EdgeTestCase{

    protected $cookie;

    public function setUp(){
        parent::setUp();
        $this->cookie = new Cookie(array(
           'secure' => false,
           'sign' => false,
           'secret' => 'C7s9r7yYYyVCDZZstzyl',
           'httpOnly' => true
       ));
    }

    public function testSet(){
        $this->cookie->set("test", "edge", time() + 200);
        $this->assertEquals("edge", $_COOKIE['test']);
        $this->assertEquals("edge", $this->cookie->get("test"));
    }

    public function testDelete(){
        $this->cookie->set("test", "edge", time() + 200);
        $this->assertArrayHasKey("test", $_COOKIE);
        $this->cookie->delete("test");
        $this->assertArrayNotHasKey("test", $_COOKIE);
    }
}