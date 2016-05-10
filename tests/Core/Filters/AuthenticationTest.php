<?php
namespace Edge\Tests\Core\Filters;

use Edge\Core\Tests\EdgeWebTestCase,
    Edge\Core\Edge,
    Edge\Core\Filters\Authentication;

class AuthenticationTest extends EdgeWebTestCase{

    protected $auth;

    public function setUp(){
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/method';
        $this->mockResponse();
        $this->auth = new Authentication([
                         "url" => "http://login/url"
                     ]);
    }

    public function tearDown(){
        parent::tearDown();
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    protected function mockResponse(){
        $stub = $this->getMockBuilder('Edge\Core\Http\Response')
                     ->setMethods(['redirect'])
                     ->getMock();
        $stub->expects($this->any())
             ->method('redirect')
             ->willReturn("http://login/url");
        Edge::app()->response = $stub;
    }

    public function testAuthenticationForGuestUserWithGet(){
        $this->auth->preProcess(Edge::app()->response, Edge::app()->request);
        $this->assertEquals($_SERVER['REQUEST_URI'], Edge::app()->session->redirectUrl);
    }

    /**
     * @expectedException \Edge\Core\Exceptions\Unauthorized
     */
    public function testAuthenticationForGuestUserWithGetAjax(){
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->auth->preProcess(Edge::app()->response, Edge::app()->request);
        $this->assertEquals($_SERVER['REQUEST_URI'], Edge::app()->session->redirectUrl);
    }

    public function testAuthenticationForGuestUserWithPost(){
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->auth->preProcess(Edge::app()->response, Edge::app()->request);
        $this->assertNotEquals($_SERVER['REQUEST_URI'], Edge::app()->session->redirectUrl);
    }

    /**
     * @expectedException \Edge\Core\Exceptions\Unauthorized
     */
    public function testAuthenticationForGuestUserWithPosttAjax(){
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->auth->preProcess(Edge::app()->response, Edge::app()->request);
        $this->assertEquals($_SERVER['REQUEST_URI'], Edge::app()->session->redirectUrl);
    }
}