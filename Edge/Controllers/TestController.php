<?php
namespace Edge\Controllers;

class TestController extends BaseController{

    public function testJson(array $params){
        return $params;
    }

    public function get(){
        return "Test get";
    }

    public function getWithParams($param1, $param2){
        return sprintf("%s %s", $param1, $param2);
    }

    public function formPost(array $attrs){
        return $attrs['username'];
    }

    public function formPostParams($name, array $attrs){
        return $name . $attrs['username'];
    }
}