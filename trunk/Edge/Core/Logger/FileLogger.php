<?php
namespace Edge\Core\Logger;


class FileLogger extends BaseLogger{

    private $file;

    public function __construct($file, $dateFormat, $logLevel){
        $this->file = $file;
        parent::__construct($dateFormat, $logLevel);
    }

    protected function writeLog($message){
        file_put_contents($this->file, $message, FILE_APPEND);
    }
}