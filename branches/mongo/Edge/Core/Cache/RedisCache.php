<?php
namespace Edge\Core\Cache;
use Edge\Core;

class RedisCache extends BaseCache {
	private $link;

	public function __construct(array $settings) {
		$this->link = new \Redis();
        list($host, $port) = explode(":", $settings[0]);
        $this->link->connect($host, (int) $port);
	}

    /**
     * Override default serialize method of parent since
     * Memcached client library takes care of it
     * @param mixed $data
     * @return mixed
     */
    protected static function serialize($data){
        if(!is_numeric($data)){
            return serialize($data);
        }
        return $data;
    }

    /**
     * Override default unserialize method of parent since
     * Memcached client library takes care of it
     * @return mixed
     */
    protected static function unserialize($data){
        if(!is_numeric($data)){
            return unserialize($data);
        }
        return $data;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function increment($key, $value = 1){
        return $this->link->incrby($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function decrement($key, $value = 1){
        return $this->link->decrby($key, $value);
    }

	public function getValue($key) {
		return $this->link->get($key);
	}

	public function setValue($key, $value, $ttl=0) {
		$res = $this->link->set($key, $value);
        if($ttl){
            $this->link->expire($key, $ttl);
        }
		return $res;
	}

	public function deleteValue($key) {
		return $this->link->del($key);
	}

	public function __destruct() {
		$this->link->close();
	}
}