<?php
namespace Edge\Core\Tests;

use Edge\Core\Edge;

abstract class EdgeTestCase extends \PHPUnit_Framework_TestCase{

    protected function initRouter(){
        $router = Edge::app()->getConfig('routerClass');
        return new $router(Edge::app()->getRoutes());
    }
}