<?php
namespace Edge\Tests\Core\Http;


class JsonRpcTransformerTest extends JsonTransformerTest{

    protected function getDriver(){
        return "jsonrpc";
    }

    protected function decodedData(){
        return [
            "jsonrpc" => "2.0",
            "method" => "edge",
            "params" => ["php"],
            "id" => "1"
        ];
    }

    public function testEncode(){
        $this->transformer->decode(json_encode($this->decodedData()));
        $this->assertEquals('{"jsonrpc":"2.0","result":"edge","id":"1"}', $this->transformer->encode("edge"));
    }

    public function testDecode(){
        $this->assertEquals($this->decodedData()['params'], $this->transformer->decode($this->encodedData()));
    }

    public function testMethod(){
        $this->transformer->decode(json_encode($this->decodedData()));
        $this->assertEquals("edge", $this->transformer->method);
    }

}