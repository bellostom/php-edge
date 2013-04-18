<?php
namespace Framework\Core;

class Settings extends Singleton{
	private $_settings;

	protected function __construct(){
		//include(dirname(__FILE__).'/config.php');
        $config = Configuration::getInstance();
		$this->_settings = $config->getSettings();
	}

	public function __get($key)	{
		return $this->_settings[$key];
	}
}
?>