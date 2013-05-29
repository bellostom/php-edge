<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 29/5/2013
 * Time: 2:11 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Http;


class Transformer {
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

    public function encode($body){
        throw new \Exception("Child class should implement encode");
    }

    public function decode($body){
        throw new \Exception("Child class should implement encode");
    }
}