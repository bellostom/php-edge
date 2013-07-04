<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController,
    Edge\Core\Cache\Validator,
    Edge\Core\Edge;

class Home extends BaseController{

    protected static $layout = 'Layouts/ui.layout.tpl';
    protected static $js = array(
        'static/js/facebook.js'
    );

    public function filters(){
        return array_merge(parent::filters(), array(
            array(
                'Edge\Core\Filters\PageCache',
                'ttl' => 10*60,
                'varyBy' => 'url',
                //'cacheValidator' => new Validator\QueryValidator("SELECT COUNT(id) FROM users"),
                'applyTo' => array('index', 'post')
            ),
            array('Edge\Core\Filters\DynamicOutput')
        ));
    }

    public function index(){
        $tpl = static::loadView('ui.test1.tpl');
        $tpl->test = 'thomas';
        return parent::render($tpl);
    }

    public static function fetchUser(){
        return time();
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