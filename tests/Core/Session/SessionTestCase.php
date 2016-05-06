<?php
namespace Edge\Tests\Core\Session;

use Edge\Core\Session\Session;
use Edge\Tests\EdgeTestCase;

abstract class SessionTestCase extends EdgeTestCase{

    protected $session;

    abstract protected function getSessionEngine();

    public function tearDown(){
        parent::tearDown();
        if(session_status() == \PHP_SESSION_ACTIVE && $this->session){
            $this->session->destroy();
        }
        $this->session = null;
    }

    public function setUp(){
        parent::setUp();
        $settings = [
            'session.name' => 'edge',
            'session.timeout' => 20*60,
            'session.httponly' => true
        ];
        $this->session = new Session($this->getSessionEngine(), $settings);
    }

    public function getSession(){
        return $this->session;
    }

    public function testAdd(){
        $session = $this->getSession();
        $session->key = 'edge';
        $this->assertEquals("edge", $_SESSION['key']);
    }

    public function testGet(){
        $session = $this->getSession();
        $session->key = "edge";
        $this->assertEquals("edge", $session->key);
    }

    public function testDelete(){
        $session = $this->getSession();
        unset($session->key);
        $this->assertNull($session->key);
    }

    public function testIsset(){
        $session = $this->getSession();
        $session->key = 'edge';
        $this->assertTrue(isset($session->key));
    }

    public function testDestroy(){
        $session = $this->getSession();
        $id = $session->getSessionId();
        $session->destroy();
        $this->assertNotEquals($id, $session->getSessionId());
    }
}