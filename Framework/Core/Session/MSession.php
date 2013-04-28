<?php
namespace Framework\Core\Session;

use Framework\Core\Singleton;
/**
 *
 * Custom session handler.
 * Save session to memcache
 * @author thomas
 *
 */
class MSession extends Singleton{
	protected $lifeTime;
	protected $memcache;

	protected function __construct(){
		session_set_save_handler(array(&$this,"open"),
			                       array(&$this,"close"),
			                       array(&$this,"read"),
			                       array(&$this,"write"),
			                       array(&$this,"destroy"),
			                       array(&$this,"gc"));
		$this->memcache = MSessionCache::getInstance();
	}

	public function open($savePath, $sessName){
		$this->lifeTime = get_cfg_var("session.gc_maxlifetime");
		return true;
	}

	public function close(){
		return true;
	}

	public function read($sessID){
		return $this->memcache->get($sessID);
	}

	public function write($sessID, $sessData){
		return $this->memcache->add($sessID, $sessData, false, $this->lifeTime);
	}

	public function destroy($sessID){
		return $this->memcache->delete($sessID);
	}

	public function __destruct(){
		session_write_close();
	}

	public function gc($sessMaxLifeTime){}
}
?>