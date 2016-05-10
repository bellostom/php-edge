<?php
namespace Edge\Tests;

use Edge\Core\Edge;

/**
 * base class for tests that need access to services
 * Class EdgeWebTestCase
 * @package Edge\Tests
 */
abstract class EdgeWebTestCase extends EdgeTestCase{

    public function setUp(){
        parent::setUp();
        $this->mockApp();
    }

    public function tearDown(){
        parent::tearDown();
        $this->destroyApp();
    }

    protected function mockApp(){
        $stub = $this->getMockBuilder('Edge\Core\Edge')
                     ->setMethods(['user'])
                     ->setConstructorArgs([$this->getConfigFile()])
                     ->getMock();

        $stub->method('user')->willReturn($this->getUser());
        return $stub;
    }

    protected function getConfigFile(){
        return include(__DIR__."/Config/config.php");
    }

    protected function destroyApp(){
        Edge::app()->session->destroy();
        Edge::app()->destroy();
    }

    /**
     * Mock user object
     * @param array $attrs
     * @param null $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUser($attrs = [], $methods = null){
        if(!$attrs){
            $attrs = [
                'id' => 1,
                'username' => 'guest'
            ];
        }
        $class = Edge::app()->getConfig('userClass');
        return $this->getMockBuilder($class)
                    ->setMethods($methods)
                    ->setConstructorArgs([$attrs])
                    ->getMock();
    }
}