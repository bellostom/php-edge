<?php
namespace Edge\Tests;

use Edge\Core\Edge;

abstract class EdgeControllerTestCase extends EdgeWebTestCase{

    protected function invokeRouter(){
        $this->mockResponse();
        $router = Edge::app()->getConfig('routerClass');
        $router = new $router(Edge::app()->getRoutes());
        $router->invoke();
    }

    protected function mockResponse(){
        $stub = $this->getMockBuilder('Edge\Core\Http\Response')
                     ->setMethods(['write'])
                     ->getMock();

        $stub->method('write')->willReturn(null);
        Edge::app()->response = $stub;
    }

    protected function mockRequest($params){
        $request = $this->getMockBuilder('Edge\Core\Http\Request')
                         ->disableOriginalConstructor()
                         ->setMethods(['getBody'])
                         ->getMock();

        $request->expects($this->any())
                      ->method('getBody')
                      ->willReturn($params);
        $request->__construct();
        Edge::app()->request = $request;
    }

    protected function post($url, $contentType, $params=null){
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['CONTENT_TYPE'] = $contentType;
        if($params){
            $this->mockRequest($params);
        }
        $this->invokeRouter();
    }

    protected function get($url, $contentType="text/html"){
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['CONTENT_TYPE'] = $contentType;
        $this->invokeRouter();
    }
}