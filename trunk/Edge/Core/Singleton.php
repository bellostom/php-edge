<?php
namespace Edge\Core;

abstract class Singleton {
	protected function __construct() {}
	protected static $instances = array();

	public static function getInstance() {
        $calledClassName = get_called_class();
        if (!isset(static::$instances[$calledClassName])){
			static::$instances[$calledClassName] = new $calledClassName();
        }
        return static::$instances[$calledClassName];
    }

    final private function __clone() {}
}
?>