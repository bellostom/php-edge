<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 12/5/2013
 * Time: 10:48 πμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Cache\Validator;

abstract class CacheValidator{

    protected $value;

    public function execute(){
        $this->value = $this->validate();
    }

    public function isCacheStale(){
        return $this->value != $this->validate();
    }

    abstract protected function validate();
}