<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController,
    Edge\Core\Cache\Validator\FileValidator;

class Home extends BaseController{

    public function __dependencies(){
        return array_merge(parent::__dependencies(), array('db'));
    }

    public function __filters(){
        return array(
            array(
                'Edge\Core\Filters\OutputCache',
                'ttl' => 10*60,
                'varyBy' => 'url',
                'cacheValidator' => new FileValidator("/var/www/frm/test.php")
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