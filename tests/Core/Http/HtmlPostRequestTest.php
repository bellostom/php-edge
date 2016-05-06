<?php
namespace Edge\Tests\Core\Http;

class HtmlPostRequestTest extends HtmlRequestTest{

    protected function setDefaults(){
        parent::setDefaults();
        $_POST = $this->getRequestBody();
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    public function tearDown(){
        parent::tearDown();
        $_POST = [];
    }

    protected function getRequestBody(){
        return [
            "username" => "test",
            "password" => "test"
        ];
    }

}