<?php
namespace Edge\Core\Exceptions;

class ThrowError extends EdgeException {
    public function __construct($message) {
        parent::__construct($message);
        trigger_error($message, E_USER_ERROR);
    }
}
?>