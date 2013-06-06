<?php
namespace Monolog;

class EdgeLogger extends Logger{

    public function addRecord($level, $message, array $context = array()){
        if(is_array($message) || is_object($message)){
            $message = var_export($message, true);
        }
        parent::addRecord($level, $message, $context);
    }

    public static function factory(array $attrs){
        $dateFormat = $attrs['dateFormat'];
        $output = "%datetime%: %level_name% - %message%\n";
        $formatter = new Formatter\LineFormatter($output, $dateFormat);
        $stream = new Handler\StreamHandler($attrs['file'], constant(__NAMESPACE__.'\Logger::'.$attrs['logLevel']));
        $stream->setFormatter($formatter);
        $log = new EdgeLogger('Edge');
        $log->pushHandler($stream);
        return $log;
    }
}