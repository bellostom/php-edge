<?php
namespace Edge\Core\Logger\Monolog;

require "Edge/Core/Logger/Monolog/Logger.php";

/**
 * Class EdgeLogger
 * Extend Monolog\Logger in order to define new options
 * and add a custom autoloader for discovering modules
 * under the Monolog namespace
 * @package Edge\Core\Logger\Monolog
 */
class EdgeLogger extends \Monolog\Logger{

    public function addRecord($level, $message, array $context = array()){
        if(is_array($message) || is_object($message)){
            $message = var_export($message, true);
        }
        parent::addRecord($level, $message, $context);
    }

    protected static function autoload(){
        spl_autoload_register(function($class){
            $file = "../Edge/Core/Logger/".str_replace('\\','/',$class).".php";
            if(is_file($file)){
                include $file;
            }
        });
    }

    public static function factory(array $attrs){
        static::autoload();
        $dateFormat = $attrs['dateFormat'];
        $output = "%datetime%: %level_name% - %message%\n";
        $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat);
        $stream = new \Monolog\Handler\StreamHandler($attrs['file'],
                                                    constant('\Monolog\Logger::'.$attrs['logLevel']));
        $stream->setFormatter($formatter);
        $log = new EdgeLogger('Edge');
        $log->pushHandler($stream);
        return $log;
    }
}