<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController;

class Home extends BaseController{

    protected function dependencies(){
        return array_merge(parent::dependencies(), array('db'));
    }

    protected function filters(){
        return array(
            array(
                'Edge\Core\Cache\OutputCache',
                'ttl' => 3600,
                'varyBy' => 'url'
            )
        );
    }

    public function index(){
        return 'hello world';
    }

    public function test(){
        return 'hello test';
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