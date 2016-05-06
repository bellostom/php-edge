<?php
namespace Edge\Tests\Core\Http;


class JsonTransformerTest extends TransformerTestCase{

    protected function getDriver(){
        return "json";
    }

    protected function decodedData(){
        return [
            "framework" => "edge",
            "type" => "php"
        ];
    }

    protected function encodedData(){
        return json_encode($this->decodedData());
    }

}