<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 12/5/2013
 * Time: 10:52 πμ
 * To change this template use File | Settings | File Templates.
 */

namespace Framework\Core\Cache\Validator;

class ExpressionValidator extends CacheValidator{

    private $expression;

    public function __construct($expression){
        $this->expression = $expression;
    }

    protected function validate(){
        return eval($this->expression);
    }
}