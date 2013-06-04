<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 3/6/2013
 * Time: 3:24 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Monolog;

class EdgeLogger extends Logger{

    public function addRecord($level, $message, array $context = array()){
        if(is_array($message) || is_object($message)){
            $message = var_export($message, true);
        }
        parent::addRecord($level, $message, $context);
    }
}