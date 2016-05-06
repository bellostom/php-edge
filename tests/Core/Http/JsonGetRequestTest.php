<?php
namespace Edge\Tests\Core\Http;

class JsonGetRequestTest extends RequestTestCase{

    protected function setDefaults(){
        $_SERVER['REQUEST_URI'] = '/some/url';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_TYPE'] = "application/json";
    }

    protected function getTransformer(){
        return 'Edge\Core\Http\JsonTransformer';
    }

    protected function getRequestBody(){
        return '[]';
    }

}