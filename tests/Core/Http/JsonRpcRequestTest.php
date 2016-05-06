<?php
namespace Edge\Tests\Core\Http;

class JsonRpcRequestTest extends RequestTestCase{

    protected function setDefaults(){
        $_SERVER['REQUEST_URI'] = '/some/url';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = "application/json";
    }

    protected function getTransformer(){
        return 'Edge\Core\Http\JsonRpcTransformer';
    }

    protected function getRequestBody(){
        return '{"jsonrpc": "2.0", "method": "sum", "params": [3, 4], "id": "1"}';
    }

    public function testIsJsonRpc(){
        $this->assertTrue($this->request->isJsonRpc());
    }

}