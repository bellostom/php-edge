<?php
namespace Edge\Core\Tests;

use Edge\Core\Edge;

abstract class EdgeTestCase extends \PHPUnit_Framework_TestCase{

    protected function initRouter(){
        $router = Edge::app()->getConfig('routerClass');
        return new $router(Edge::app()->getRoutes());
    }

    protected function setAccessible($class, $method){
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }
}