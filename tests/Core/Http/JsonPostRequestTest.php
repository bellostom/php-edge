<?php
namespace Edge\Tests\Core\Http;

class JsonPostRequestTest extends JsonGetRequestTest{

    protected function setDefaults(){
        parent::setDefaults();
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    public function testBodyValue(){
        $this->assertEquals("edge", $this->request->getParams()['value']);
    }

    protected function getRequestBody(){
        return '{"value":"edge"}';
    }

}