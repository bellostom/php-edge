<?php
namespace Edge\Core\Logger;


abstract class BaseLogger{

    protected static $LOG_LEVELS = [
        'ERROR' => 1,
        'WARNING' => 2,
        'INFO' => 4,
        'DEBUG' => 8
    ];

    protected $dateFormat;
    protected $logLevel;

    public function __construct($dateFormat, $logLevel){
        $this->dateFormat = $dateFormat;
        $this->logLevel = static::$LOG_LEVELS[$logLevel];
    }

    protected function doLog($level, $message){
        if($this->logLevel < static::$LOG_LEVELS[$level]){
            return;
        }
        $this->writeLog($this->formatMessage($level, $message));
    }

    abstract protected function writeLog($message);

    public function err($message){
        $this->doLog('ERROR', $message);
    }

    public function info($message){
        $this->doLog('INFO', $message);
    }

    public function debug($message){
        $this->doLog('DEBUG', $message);
    }

    public function warn($message){
        $this->doLog('WARNING', $message);
    }

    protected function formatMessage($level, $message){
        $level = strtoupper($level);
        $message = static::format($message);
        return "[{$this->getTimestamp()}] [{$level}] {$message}".PHP_EOL;
    }

    protected static function format($message){
        if(is_array($message) || is_object($message)){
            $message = var_export($message, true);
        }
        return $message;
    }

    protected function getTimestamp(){
        return date($this->dateFormat);
    }

}