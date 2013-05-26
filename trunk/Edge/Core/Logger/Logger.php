<?php
namespace Edge\Core\Logger;

class Logger{
    private $logger;

    public function __construct($file, $identity, $dateFormat){
        $conf = array(
            'mode' => 0600,
            'timeFormat' => $dateFormat,
            'locking' => true
        );
        $this->logger = Log::singleton('file', $file, $identity, $conf);
    }

	public function log($message, $type=null){
		if(is_null($type))
			$type = PEAR_LOG_CRIT;
		$this->logger->log($message, $type);
	}
}
?>