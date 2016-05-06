<?php
namespace Edge\Tests\Core\Http;


class HtmlTransformerTest extends TransformerTestCase{

    protected function getDriver(){
        return "html";
    }

    protected function decodedData(){
        return [];
    }

    protected function encodedData(){
        return [];
    }

}