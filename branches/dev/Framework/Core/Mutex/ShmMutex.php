<?php
class ShmMutex extends Mutex{

	public function __construct($key){
		parent::__construct((int) $key);
	}

	public function get_lock(){
		$this->mutex = sem_get($this->key, 1);
		return sem_acquire($this->mutex);
	}

	public function release_lock(){
		@sem_release($this->mutex);
		$this->mutex = null;
	}
}
?>