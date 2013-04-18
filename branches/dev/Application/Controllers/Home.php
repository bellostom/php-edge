<?php
namespace Application\Controllers;

use Framework\Controllers as Base;

class Home extends Base\BaseController{

    public function index(){
        return 'hello world';
    }

    /**
     * Framework\Core\Interfaces\I18n
     * Override default behavior
     */
    public function translate(){}
}
?>