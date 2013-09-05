<?php
namespace Edge\Core\Cache;
use Edge\Core;

class MemoryCache extends BaseCache {
	private $link;

	public function __construct(array $settings) {
        parent::__construct($settings['namespace']);
		$this->link = new \Memcache();
		foreach($settings['servers'] as $server){
			list($server, $port, $weight) = explode(':', $server);
			$this->link->addServer($server, (int) $port, 0, (int) $weight);
		}
	}

    /**
     * Override default serialize method of parent since
     * Memcached client library takes care of it
     * @param mixed $data
     * @return mixed
     */
    protected static function serialize($data){
        return $data;
    }

    /**
     * Override default unserialize method of parent since
     * Memcached client library takes care of it
     * @return mixed
     */
    protected static function unserialize($data){
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
        return $this->link->increment($key, $value);
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
        return $this->link->decrement($key, $value);
    }

	public function getValue($key) {
		return $this->link->get($key);
	}

	public function setValue($key, $value, $ttl=0) {
		return $this->link->set($key, $value, 0, $ttl);
	}

	public function deleteValue($key) {
		return $this->link->delete($key, 0);
	}

    protected function getLock($key, $ttl){
        return $this->link->add($key, true, 0, $ttl);
    }

	public function __destruct() {
		$this->link->close();
	}
}