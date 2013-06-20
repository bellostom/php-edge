<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController,
    Edge\Core\Cache\Validator;

class Home extends BaseController{

    public function __filters(){
        return array_merge(parent::__filters(), array(
            array(
                'Edge\Core\Filters\PageCache',
                'ttl' => 10*60,
                'varyBy' => 'url',
                'cacheValidator' => new Validator\QueryValidator("SELECT COUNT(id) FROM users"),
                'applyTo' => array('index', 'post')
            )
        ));
    }

    public function index(){
        //print \Edge\Core\Edge::app()->session->getSessionId();
        //return \Edge\Models\User::find(1);
        //\Edge\Core\Edge::app()->request->setCookie("yo", json_encode(array(1,2)), time()+20*60);
        print \Edge\Core\Edge::app()->request->getCookie("yo");
        //\Edge\Core\Edge::app()->request->deleteCookie("yo");
        $tpl = new \Edge\Core\Template('Application/Views/ui.test1.tpl');
        $tpl->test = 'thomas';
        return $tpl->parse();
        return 'hello world';
    }

    public static function fetchUser(){
        return time();
    }

    public function test(){
        $db = \Edge\Core\Edge::app()->writedb;
        $db->startTransaction();
        $r = new \Edge\Models\User();
        $r->username = 'thomas';
        $r->save();
        //throw new \Exception("error");
        return 1;


        $r = \Edge\Models\User::getUserByUsername("thomas");
        print "Updating ".$r->name."<br/>";
        $r->pass="test";
        $r->delete();

        return 1;



        $r = \Edge\Models\User::find('all', array(
            'conditions' => array('name' => 'thomas')
        ));
        print $r->name;
        return 1;
        $r = \Edge\Models\User::find('all', array(
            'fetchMode' => \Edge\Models\User::FETCH_RESULTSET,
            'cache' => array(
                'ttl' => 100
            )
        ));
        foreach($r as $user){
            echo $user->name."<br>";
        }
        //var_dump($r);
        return 1;
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