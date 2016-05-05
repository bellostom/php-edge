<?php
namespace Edge\Tests\Core\Session;

use Edge\Core\Session\Session;

abstract class SessionTestCase extends \PHPUnit_Framework_TestCase{

    abstract protected function getSessionEngine();

    protected function getSession(){
        static $_session = null;
        if($_session == null){
            $_SESSION = [];
            $settings = [
                'session.name' => 'edge',
                'session.timeout' => 20*60,
                'session.httponly' => true
            ];
            $_session = new Session($this->getSessionEngine(), $settings);
        }
        return $_session;
    }

    public function testAdd(){
        $session = $this->getSession();
        $session->key = 'edge';
        $this->assertEquals("edge", $_SESSION['key']);
    }

    public function testGet(){
        $session = $this->getSession();
        $this->assertEquals("edge", $session->key);
    }

    public function testDelete(){
        $session = $this->getSession();
        unset($session->key);
        $this->assertNull($session->key);
    }

    public function testDestroy(){
        $session = $this->getSession();
        $id = $session->getSessionId();
        $session->destroy();
        $this->assertNotEquals($id, $session->getSessionId());
    }

    public static function tearDownAfterClass(){
        if(session_status() == \PHP_SESSION_ACTIVE){
            session_unset();
            session_destroy();
        }
    }
}