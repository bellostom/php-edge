<?php
namespace Edge\Controllers;

use Edge\Core\Edge;

abstract class AccessControlController extends AuthController{

    public function filters(){
        return array_merge(parent::filters(), array(
            array(
                'Edge\Core\Filters\AccessControl',
                "permissions" => $this->getAclMap()[Edge::app()->router->getAction()]
            )
        ));
    }

    /**
     * Return a method to permission mapping
     * Example
     * return array(
        'index' => array('create user')
       );
     * @return array
     */
    abstract protected function getAclMap();
}