<?php
namespace Edge\Tests\Core\Http;

use Edge\Tests\EdgeTestCase;

abstract class RequestTestCase extends EdgeTestCase{

    protected $request;

    public function setUp(){
        $this->setDefaults();
        $data = $this->getRequestBody();
        $this->request = $this->getMockBuilder('Edge\Core\Http\Request')
                              ->disableOriginalConstructor()
                              ->setMethods(['getBody'])
                              ->getMock();

        $this->request->expects($this->any())
              ->method('getBody')
             ->willReturn($data);
        $this->request->__construct();
    }

    abstract protected function setDefaults();
    abstract protected function getTransformer();
    abstract protected function getRequestBody();

    public function testRequestUrl(){
        $this->assertEquals($_SERVER['REQUEST_URI'], $this->request->getRequestUrl());
    }

    public function testContentType(){
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $this->request->getContentType());
    }

    public function testHttpMethod(){
        $this->assertTrue($this->request->is($_SERVER['REQUEST_METHOD']));
    }

    public function testTransformer(){
        $this->assertInstanceOf($this->getTransformer(), $this->request->getTransformer());
    }

    public function testParams(){
        $this->assertEquals($this->request->getTransformer()->decode($this->getRequestBody()), $this->request->getParams());
    }

    public function testBody(){
        $this->assertEquals($this->getRequestBody(), $this->request->getBody());
    }

    public function testGetHttpMethod(){
        $this->assertEquals($_SERVER['REQUEST_METHOD'], $this->request->getHttpMethod());
    }

}