<?php
namespace Application\Controllers;

use Edge\Controllers\BaseController;

class Home extends BaseController{

    protected function dependencies(){
        return array_merge(parent::dependencies(), array('db'));
    }

    public function index(){
        return 'hello world';
    }
}