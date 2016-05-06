<?php
namespace Edge\Tests\Core\Http;

class HtmlRequestTest extends RequestTestCase{

    protected function setDefaults(){
        $_SERVER['REQUEST_URI'] = '/some/url';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_TYPE'] = "text/html";
    }

    protected function getTransformer(){
        return 'Edge\Core\Http\HtmlTransformer';
    }

    public function testIsAjax(){
        $this->assertFalse($this->request->isAjax());
    }

    protected function getRequestBody(){
        return [];
    }

}