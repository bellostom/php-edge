<?php
namespace Edge\Controllers;

class TestController extends BaseController{

    public function filters(){
        return array_merge(parent::filters(), [
            array(
                'Edge\Core\Filters\CsrfProtection',
                'applyTo' => ['testCsrf']
            )
        ]);
    }

    public function testCsrf(array $params){
        return $params;
    }

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

    public function formLogin($param, $attrs=null){
        if($attrs && is_array($attrs)){
            return $attrs['username'];
        }
        return $param;
    }
}