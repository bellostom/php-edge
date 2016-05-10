<?php
namespace Edge\Tests\Core\Filters;

use Edge\Core\Tests\EdgeWebTestCase,
    Edge\Core\Edge,
    Edge\Core\Filters\CsrfProtection;

class CsrfProtectionTest extends EdgeWebTestCase{

    protected $filter;

    public function setUp(){
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/method';
        $this->filter = new CsrfProtection([
                         "tokenName" => "token"
                     ]);
    }

    protected function mockRequest(array $retVal){
        $stub = $this->getMockBuilder('Edge\Core\Http\Request')
                     ->setMethods(['getParams', 'getCsrfToken'])
                     ->getMock();

        $stub->method('getParams')
             ->willReturn($retVal);

        $stub->expects($this->any())
             ->method('getCsrfToken')
             ->willReturn("someTokenValue");
        return $stub;
    }

    /**
     * @expectedException \Edge\Core\Exceptions\BadRequest
     * @expectedExceptionMessage The body does not contain a CSRF token
     */
    public function testWithoutToken(){
        $request = $this->mockRequest(["username" => "test", "differentToken" => "invalid"]);
        $this->filter->preProcess(Edge::app()->response, $request);
    }

    /**
     * @expectedException \Edge\Core\Exceptions\BadRequest
     * @expectedExceptionMessage The specified CSRF token is not valid
     */
    public function testWithInvalidToken(){
        $request = $this->mockRequest(["username" => "test", "token" => "invalid"]);
        $this->filter->preProcess(Edge::app()->response, $request);
    }

    public function testValidToken(){
        $request = $this->mockRequest(["username" => "test", "token" => "someTokenValue"]);
        $this->assertTrue($this->filter->preProcess(Edge::app()->response, $request));
    }

    public function testGetMethod(){
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = $this->mockRequest(["username" => "test", "token" => "someTokenValue"]);
        $this->assertNull($this->filter->preProcess(Edge::app()->response, $request));
    }
}