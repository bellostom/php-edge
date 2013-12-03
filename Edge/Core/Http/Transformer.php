<?php
namespace Edge\Core\Http;

abstract class Transformer {
    private static $driver;

    public static function factory($driver){
        switch($driver){
            case 'json':
                $class = 'JsonTransformer';
                break;
            case 'jsonrpc':
                $class = 'JsonRpcTransformer';
                break;
            case 'xml':
                $class = 'XmlTransformer';
                break;
            default:
                $class = 'HtmlTransformer';
        }
        $class = __NAMESPACE__."\\".$class;
        self::$driver = new $class;
        return self::$driver;
    }

    abstract public function encode($body);

    abstract public function decode($body);
}