<?php
namespace Edge\Tests\Core\Http;

use Edge\Core\Http\Cookie,
    Edge\Core\Tests\EdgeWebTestCase;

class SignedCookieTest extends EdgeWebTestCase{

    protected $cookie;

    public function setUp(){
        parent::setUp();
        $this->cookie = new Cookie(array(
           'secure' => false,
           'sign' => true,
           'secret' => 'C7s9r7yYYyVCDZZstzyl',
           'httpOnly' => true
       ));
    }

    public function testSet(){
        $this->cookie->set("test", "edge", 1607731200);
        $this->assertEquals("edge", $_COOKIE['test']);
    }

    public function testGet(){
        $_COOKIE['test'] = '.ZWRnZXxffDE2MDc3MzEyMDB8X3wwMTI2ODliNmRkYTFiZWEyODE3NmZjNDExZjVhNmQxM2IyYTMwYmI4';
        $this->assertEquals("edge", $this->cookie->get("test"));
    }

    public function testExpired(){
        $_COOKIE['test'] = '.ZWRnZXxffDkxMzQyMDgwMHxffDAxMjY4OWI2ZGRhMWJlYTI4MTc2ZmM0MTFmNWE2ZDEzYjJhMzBiYjg=';
        $this->assertFalse($this->cookie->get("test"));
    }

    public function testEmpty(){
        $_COOKIE['test'] = '';
        $this->assertFalse($this->cookie->get("test"));
    }
}