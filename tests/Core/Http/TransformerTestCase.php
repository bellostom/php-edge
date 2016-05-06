<?php
namespace Edge\Tests\Core\Http;

use Edge\Core\Http\Transformer;
use Edge\Tests\EdgeTestCase;

abstract class TransformerTestCase extends EdgeTestCase{

    protected $transformer;

    public function setUp(){
        $this->transformer = Transformer::factory($this->getDriver());
    }

    abstract protected function getDriver();
    abstract protected function decodedData();
    abstract protected function encodedData();

    public function testEncode(){
        $this->assertEquals($this->encodedData(), $this->transformer->encode($this->decodedData()));
    }

    public function testDecode(){
        $this->assertEquals($this->decodedData(), $this->transformer->decode($this->encodedData()));
    }

}