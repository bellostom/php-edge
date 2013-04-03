<?php
namespace Framework\Core\Mutex;

class McacheMutex extends Mutex{

	public function get_lock(array &$callback=null){
		$retval = array(
			'acquired' => false,
			'data' => false
		);
        if(!is_null($callback)) {
            $dt = call_user_func_array($callback['method'], $callback['args']);
            if($dt) {
                throw new CacheExists("Cache created by other thread");
            }
        }
		$max_tries = 10;
		$tries = 1;
		$cache = MCache::getInstance();
		$acquired = $cache->addIfKeyNotExists($this->key, 1, 0, 30);
		while(!$acquired && $tries < $max_tries){
			usleep(600);
			$tries++;
			if(!is_null($callback)) {
				$dt = call_user_func_array($callback['method'], $callback['args']);
				if($dt) {
					throw new CacheExists("Cache created by other thread");
				}
			}
			$acquired = $cache->addIfKeyNotExists($this->key, 1, 0, 30);
		}
		if(!$acquired){
			throw new MaxAttemptsExceeded("Exceeded maximum tries to acquire lock");
		}
		$retval['acquired'] = true;
		return $retval;
	}

	public function release_lock(){
		$cache = MCache::getInstance();
		$cache->delete($this->key);
		$this->mutex = null;
	}
}
?>