<?php
namespace Edge\Core\Cache;
use Edge\Core;

class RedisCache extends BaseCache {
	private $link;

	public function __construct(array $settings) {
		$this->link = new \Redis();
        list($host, $port) = explode(":", $settings["server"]);
        $this->link->connect($host, (int) $port);
        parent::__construct($settings['namespace']);
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
        $key = parent::getNsKey($key);
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
        $key = parent::getNsKey($key);
        return $this->link->decrby($key, $value);
    }

	public function getValue($key) {
		return $this->link->get($key);
	}

	public function setValue($key, $value, $ttl=0) {
        if($ttl == 0){
            $ttl = 31536000;
        }
		return $this->link->setex($key, $ttl, $value);
	}

	public function deleteValue($key) {
		return $this->link->del($key);
	}

    protected function getLock($key, $ttl){
        $ret = $this->link->setnx($key, true);
        if($ret){
            $this->link->expire($key, $ttl);
        }
        return $ret;
    }

	public function __destruct() {
		$this->link->close();
	}
}