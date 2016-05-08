<?php
namespace Edge\Tests\Controllers;

use Edge\Controllers\TestController;
use Edge\Core\Edge,
    Edge\Tests\EdgeControllerTestCase;

class ControllerTest extends EdgeControllerTestCase{

    public function testJsonPost(){
        $params = json_encode(["edge" => "framework"]);
        $this->post("/test/json", "application/json", $params);
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals($params, $response->body);
        $this->assertTrue(is_string($response->body));
        $link = TestController::createLink('\Edge\Controllers\TestController', 'testJson', [], 'POST');
        $this->assertEquals("/test/json", $link);
    }

    public function testGet(){
        $this->get("/test/get");
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("Test get", $response->body);
        $link = TestController::createLink('\Edge\Controllers\TestController', 'get');
        $this->assertEquals("/test/get", $link);
        $this->assertEquals("get", Edge::app()->router->getAction());
        $this->assertEquals(null, Edge::app()->router->getPermissions());
        $this->assertInstanceOf('\Edge\Controllers\TestController', Edge::app()->router->getController());
    }

    public function testGetWithParams(){
        $this->get("/test/edge/framework");
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("edge framework", $response->body);
        $link = TestController::createLink('\Edge\Controllers\TestController', 'getWithParams',
                                           [":param1" => "edge", ":param2" => "framework"]);
        $this->assertEquals("/test/edge/framework", $link);
    }

    public function testPost(){
        $this->post("/form/post", "application/x-www-form-urlencoded", ["username" => "user", "password" => "test"]);
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("user", $response->body);
        $this->assertEquals([["username" => "user", "password" => "test"]], Edge::app()->router->getArgs());
    }

    public function testPostWithUrlParams(){
        $this->post("/form/post/edge", "application/x-www-form-urlencoded", ["username" => "user", "password" => "test"]);
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("edgeuser", $response->body);
        $link = TestController::createLink('\Edge\Controllers\TestController', 'formPostParams',
                                           [":name" => "edge"], 'POST');
        $this->assertEquals("/form/post/edge", $link);
    }

    public function testAnyHttpMethodWithPost(){
        $this->post("/form/login/edge", "application/x-www-form-urlencoded", ["username" => "user", "password" => "test"]);
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("user", $response->body);
        $link = TestController::createLink('\Edge\Controllers\TestController', 'formLogin',
                                           [":name" => "edge"], '*');
        $this->assertEquals("/form/login/edge", $link);
    }

    public function testAnyHttpMethodWithGet(){
        $this->get("/form/login/edge");
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("edge", $response->body);
        $link = TestController::createLink('\Edge\Controllers\TestController', 'formLogin',
                                           [":name" => "edge"], '*');
        $this->assertEquals("/form/login/edge", $link);
    }

    /**
     * @expectedException1 \Edge\Core\Exceptions\BadRequest
     * @expectedExceptionMessage1 The body does not contain a CSRF token
     */
    public function testFilterFail(){
        $data = json_encode(["username" => "test"]);
        $this->post("/test/csrf", "application/json", $data);
        $response = Edge::app()->response;
        $this->assertEquals(400, $response->httpCode);
        $this->assertEquals("The body does not contain a CSRF token", $response->body);
    }
}