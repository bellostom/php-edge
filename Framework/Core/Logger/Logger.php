<?php
namespace Framework\Core\Logger;

use Framework\Core\Settings;

class Logger{
	public static $ERROR = PEAR_LOG_CRIT;

	public static function log($message, $type=null){
		if(is_null($type))
			$type = Logger::$ERROR;
		$settings = Settings::getInstance();
		$conf = array(
			'mode' => 0600,
			'timeFormat' => $settings->logger->dateFormat,
			'locking' => true
		);
		$logger = Log::singleton('file', $settings->logger->file,
								  $settings->logger->identity, $conf);
		$logger->log($message, $type);
	}
}
?>