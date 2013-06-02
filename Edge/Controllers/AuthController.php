<?php
/**
 *
 * Class to implement access control to restricted
 * areas. Extend this class for all servlets that
 * need authorization check.
 * @author thomas
 *
 */
namespace Edge\Controllers;

class AuthController extends BaseController{

    public function __filters(){
        return array(
            array(
                'Edge\Core\Filters\Authorization'
            )
        );
    }
}