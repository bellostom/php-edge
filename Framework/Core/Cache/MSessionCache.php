<?php
namespace Framework\Core\Session;

use Framework\Core\Settings;
/**
 *
 * Interface to be used with Memcached
 * as session handler
 * @author thomas
 *
 */
class MSessionCache extends MCache{

	protected function __construct(){
		$settings = Settings::getInstance();
		$this->link = new \Memcache();
		foreach($settings->session_servers as $server){
			list($server, $port, $weight) = explode(':', $server);
			$this->link->addServer($server, (int) $port, 0, (int) $weight);
		}
	}
}
?>