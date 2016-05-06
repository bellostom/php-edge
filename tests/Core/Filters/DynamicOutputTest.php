<?php
namespace Edge\Tests\Core\Filters;

use Edge\Tests\EdgeWebTestCase,
    Edge\Core\Edge,
    Edge\Core\Filters\DynamicOutput;

class DynamicOutputTest extends EdgeWebTestCase{

    protected $filter;

    public function setUp(){
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->filter = new DynamicOutput();
    }

    protected function mockResponse(){
        $stub = $this->getMockBuilder('Edge\Core\Http\Response')
                     ->setMethods(null)
                     ->getMock();

        $stub->body = '<html><head></head><body><p>{{\Edge\Tests\Core\Filters\DynamicOutputTest::inject}}</p></body></html>';
        return $stub;
    }

    public function testFragmentReplacement(){
        $response = $this->mockResponse();
        $this->filter->postProcess($response, Edge::app()->request);
        $this->assertRegexp('/edge fragment/', $response->body);
    }

    public static function inject(){
        return "edge fragment";
    }

}