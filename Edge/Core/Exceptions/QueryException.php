<?php
namespace Edge\Core\Exceptions;

use Edge\Core\Edge;

/**
 * Class QueryException
 * Log any DB query error, but return a default message to be returned to the caller
 * to avoid exposing sensitive parts of the DB
 * @package Edge\Core\Exceptions
 */
class QueryException extends EdgeException {

    public function __construct($message)
    {
        Edge::app()->logger->err($message. "\\n". $this->getTraceAsString());
        parent::__construct("Query Exception", false, false);
    }
}