<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 12/5/2013
 * Time: 10:52 πμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Cache\Validator;

class ExpressionValidator extends CacheValidator{

    private $expression;

    public function __construct($expression){
        $this->expression = $expression;
    }

    public function evaluateExpression($_expression_,$_data_=array())
    {
        if(is_string($_expression_))
        {
            extract($_data_);
            return eval('return '.$_expression_.';');
        }
        else
        {
            $_data_[]=$this;
            return call_user_func_array($_expression_, $_data_);
        }
    }

    protected function validate(){
        return eval($this->expression);
    }
}