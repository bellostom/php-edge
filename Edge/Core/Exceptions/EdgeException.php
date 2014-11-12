<?php
namespace Edge\Core\Exceptions;
use Edge\Core\Edge;

class EdgeException extends \Exception{

	public function __construct($message, $logError=true, $logBackTrace=true) {
        parent::__construct($message);
        if($logBackTrace) {
	        ob_start();
	        debug_print_backtrace();
	        $parsed = ob_get_contents();
	        ob_end_clean();
            Edge::app()->logger->err($parsed);
        }
        elseif($logError){
            Edge::app()->logger->err($this->message);
        }
    }
}