<?php
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