<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController,
    Edge\Core\Cache\Validator,
    Edge\Core\Edge;

class Home extends BaseController{

    protected static $layout = 'Application/Views/Layouts/ui.layout.tpl';
    protected static $js = array(
        'Application/Views/facebook.js'
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
        $tpl = static::loadView('Application/Views/ui.test1.tpl');
        $tpl->test = 'thomas';
        return $tpl->parse();
    }

    public static function fetchUser(){
        return time();
    }

    public function city($cityID){
        //$city = \Application\Models\City::getItemById($cityID);
        //print $city->region->name;
        $country = \Application\Models\Country::getItemById($cityID);
        foreach($country->cities as $city){
            echo $city->name;
        }
        exit;
        $city->region = "thomas";
        print $city->region->name;
        exit;
        foreach($city->region as $region){
            print $region->name;
        }
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