<?php
namespace Edge\Controllers;

abstract class AuthController extends BaseController{

    public function filters(){
        return array(
            array(
                'Edge\Core\Filters\Authentication',
                'url' => $this->getLoginUrl()
            )
        );
    }

    /**
     * Define the url for the login page
     * @return mixed
     */
    abstract protected function getLoginUrl();
}