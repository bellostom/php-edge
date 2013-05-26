<?php
namespace Edge\Core\Exceptions;

class ThrowError extends AppException {
    public function __construct($message) {
        parent::__construct($message);
        trigger_error($message, E_USER_ERROR);
    }
}
?>