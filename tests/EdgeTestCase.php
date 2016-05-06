<?php
namespace Edge\Tests;

use Edge\Core\Edge;

abstract class EdgeTestCase extends \PHPUnit_Framework_TestCase{

    protected function mockApp(){
        $stub = $this->getMockBuilder('Edge\Core\Edge')
                     ->setMethods(['user'])
                     ->setConstructorArgs([include(__DIR__."/Config/config.php")])
                     ->getMock();

        $stub->method('user')->willReturn($this->getUser());
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