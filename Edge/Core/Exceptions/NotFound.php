<?php
namespace Edge\Core\Exceptions;

use Edge\Core\Edge;

class NotFound extends EdgeException {

    public function __construct($msg){
        parent::__construct($msg);
        Edge::app()->response->httpCode = 404;
    }
}
?>