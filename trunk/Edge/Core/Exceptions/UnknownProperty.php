<?php
namespace Edge\Core\Exceptions;

class UnknownProperty extends ThrowError {

    public function __construct($attr, $class){
        $msg = sprintf("Unknown property %s in class %s", $attr, $class);
        parent::__construct($msg);
    }
}