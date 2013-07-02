<?php
namespace Edge\Core\Session;

abstract class Mutex{
	public $key;
	protected $mutex = null;

	public function __construct($key){
		$this->key = $key;
	}

	abstract public function get_lock();
	abstract public function release_lock();

	public function __destruct(){
		if(!is_null($this->mutex))
			$this->release_lock();
	}
}
?>