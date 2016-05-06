<?php
namespace Edge\Tests;

abstract class EdgeWebTestCase extends EdgeTestCase{

    public function setUp(){
        parent::setUp();
        parent::mockApp();
    }

    public function tearDown(){
        parent::tearDown();
        parent::destroyApp();
    }
}