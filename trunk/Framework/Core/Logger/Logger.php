<?php
namespace Framework\Core\Logger;

use Framework\Core\Settings;

class Logger{

	public static function log($message, $type=null){
        $settings = Settings::getInstance();
        $conf = array(
            'mode' => 0600,
            'timeFormat' => $settings->logger->dateFormat,
            'locking' => true
        );
        $logger = Log::singleton('file', $settings->logger->file,
            $settings->logger->identity, $conf);
		if(is_null($type))
			$type = PEAR_LOG_CRIT;


		$logger->log($message, $type);
	}
}
?>