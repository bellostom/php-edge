<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController;

class Home extends BaseController{

    protected static $layout = 'Layouts/ui.layout.tpl';

    /**
     * Render the layout with the contents
     * of Application/Views/ui.index.tpl
     * @return mixed|string
     */
    public function index(){
        $q = "INSERT INTO users(username) values(:username)";
        $data= [':username'=>"admin"];
        $db = \Edge\Core\Edge::app()->writedb;
        var_dump($db->dbMetaData('users'));
        exit;
        $db->dbQuery($q, $data);
        return;
        $u = new \Edge\Models\User();
        $u->username = 'admin';
        $u->save();
        return;
        \Edge\Models\User::select()
            ->where("name")
            ->in(["admin", "guest"])
            ->run();
        $tpl = static::loadView('ui.index.tpl');
        $tpl->title = 'Welcome to Edge';
        return parent::render($tpl, [
            'title' => 'Edge MVC'
        ]);
    }
}