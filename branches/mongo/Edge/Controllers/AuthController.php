<?php
namespace Edge\Controllers;

abstract class AuthController extends BaseController{

    public function filters(){
        return array(
            array(
                'Edge\Core\Filters\Authorization'
            )
        );
    }
}