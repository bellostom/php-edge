<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController,
    Edge\Core\Cache\Validator;

class Home extends BaseController{

    public function __dependencies(){
        return array_merge(parent::__dependencies(), array('db'));
    }

    public function __filters(){
        return array_merge(parent::__filters(), array(
            array(
                'Edge\Core\Filters\PageCache',
                'ttl' => 10*60,
                'varyBy' => 'url',
                //'cacheValidator' => new Validator\QueryValidator("SELECT COUNT(id) FROM users"),
                'applyTo' => array('index')
            )
        ));
    }

    public function index(){
        $tpl = new \Edge\Core\Template('Application/Views/ui.test1.tpl');
        return $tpl->parse();
        return 'hello world';
    }

    public static function fetchUser(){
        return time();
    }

    public function test(){
        $tpl = new \Edge\Core\Template('Application/Views/ui.test.tpl', array(
                'ttl' => 10*60,
                'varyBy' => 'url',
                //'cacheValidator' => new Validator\QueryValidator("SELECT COUNT(id) FROM users")
            ));
        $tpl->test = "thomas";
        return $tpl->parse();
    }

    public function post($id, $attrs){
        return array(
            "thomas" => "bellos",
            "hello" => "there",
            "data" => array(
                "name" => "Stergios",
                "surname" => "Bellos"
            )
        );
    }
}