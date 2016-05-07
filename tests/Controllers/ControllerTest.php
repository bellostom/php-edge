<?php
namespace Edge\Tests\Controllers;

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
    }

    public function testGet(){
        $this->get("/test/get");
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("Test get", $response->body);
    }

    public function testGetWithParams(){
        $this->get("/test/edge/framework");
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("edge framework", $response->body);
    }

    public function testPost(){
        $this->post("/form/post", "application/x-www-form-urlencoded", ["username" => "user", "password" => "test"]);
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("user", $response->body);
    }

    public function testPostWithUrlParams(){
        $this->post("/form/post/edge", "application/x-www-form-urlencoded", ["username" => "user", "password" => "test"]);
        $response = Edge::app()->response;
        $this->assertEquals(200, $response->httpCode);
        $this->assertEquals("edgeuser", $response->body);
    }
}