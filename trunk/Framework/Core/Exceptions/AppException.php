<?php
namespace Framework\Core\Exceptions;

use Framework\Core\Logger\Logger;

class AppException extends \Exception{

	public function __construct($message, $logError=true, $logBackTrace=true) {
        parent::__construct($message);
        if($logError){
        	Logger::log($this->message);
        }
        if($logBackTrace) {
	        ob_start();
	        debug_print_backtrace();
	        $parsed = ob_get_contents();
	        ob_end_clean();
	        Logger::log($parsed);
	        Logger::log('#################### EXCEPTION END #####################');
        }
    }
}
?>