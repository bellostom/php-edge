<?php
namespace Edge\Tests;

use Edge\Core\Edge;

abstract class EdgeTestCase extends \PHPUnit_Framework_TestCase{

    protected function mockApp(){
        $stub = $this->getMockBuilder('Edge\Core\Edge')
                     ->setMethods(['user'])
                     ->setConstructorArgs([include(__DIR__."/Config/config.php")])
                     ->getMock();

        $stub->method('user')
             ->willReturn($this->getGuestUser());
    }

    protected function destroyApp(){
        Edge::app()->session->destroy();
        Edge::app()->destroy();
    }

    protected function getGuestUser(){
        $attrs = [
            'id' => 1,
            'username' => 'guest'
        ];
        $class = Edge::app()->getConfig('userClass');
        return new $class($attrs);
    }
}