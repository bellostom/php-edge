<?php
namespace Edge\Core\Cache;

use Edge\Core\Edge,
    Edge\Core\Cache\Validator\CacheValidator;

/**
 * Class BaseCache
 * Base class which handles caching.
 *
 * @package Edge\Core\Cache
 */
abstract class BaseCache {

    /**
     * A namespace to confine caches keys
     * Useful when you are using the same
     * cache instance for multiple installations
     * Each cache key is prefixed with the namespace
     * to avoid collisions
     * @var string
     */
    protected $namespace;

    /**
     * Look ahead value for cached items
     * If item will expire in 30 secs, recalculate
     * the data.
     */
    CONST CACHE_THRESHOLD = 30;

    /**
     * The number of seconds we cache the lock key
     */
    CONST LOCK_KEY_TIMEOUT = 60; //secs

    public function __construct($namespace){
        $this->namespace = $namespace;
    }

    /**
     * Serialize the data before sending them to the underlying
     * cache layer.
     * @param mixed $data
     * @return mixed
     */
    protected static function serialize($data){
        return serialize($data);
    }

    /**
     * Unserialize the data
     * @param string $data
     * @return mixed
     */
    protected static function unserialize($data){
        return unserialize($data);
    }

    protected function getNsKey($key){
        return sprintf("%s:%s", $this->namespace, $key);
    }

    /**
     * Store an item to the cache
     * Each item is actually an array with 3 values
     * 1. The actual payload
     * 2. The actual expiration time
     * 3. Optionally, a CacheValidator object
     * We store the actual expiration within the value and dictate
     * the storage engine a larger cache period.
     *
     * @param string $key The unique hey for the cached item
     * @param mixed $value The cache value
     * @param int $ttl How long the item will be cached. 0 for infinite cache. Defaults to 0
     * @param CacheValidator $cacheValidator Instance of CacheValidator (optional)
     * @return boolean
     */
    public function add($key, $value, $ttl=0, $cacheValidator=null){
        $key = $this->getNsKey($key);
        if(!is_null($cacheValidator)){
            $cacheValidator->execute();
        }
        $realTtl = 0;
        if($ttl != 0){
            $realTtl = time() + $ttl;
            $ttl = $ttl + (10*60);
        }

        $value = static::serialize(array($value, $realTtl, $cacheValidator));
        return $this->setValue($key, $value, $ttl);
    }

    /**
     * Get the cached value from the underlying storage.
     * You should define lock to be true for expensive
     * to calculate operations, as it will protect against
     * cache stampedes.
     * To accomplish that, the 1st thread that determines that
     * the item needs to be recalculated, acquires a lock and resaves
     * the item with a new expiration. This way any threads that access
     * the item, during the current one refreshes the cache, will return
     * the old value and will not try to calculate it again.
     *
     * WARNING: We do not delete the lock key. Rather we let the storage
     * engine expire it. We do this to avoid race condition situations that
     * lead to 2 or more threads recalculating an already generated one, after
     * a lock is deleted.
     *
     * @param string $key
     * @param boolean $lock
     * @return mixed
     */
    public function get($key, $lock=false){
        $nsKey = $this->getNsKey($key);
        $value = $this->getValue($nsKey);
        if($value){
            list($data, $expires, $validator) = static::unserialize($value);
            if($expires != 0 && (time() > $expires)){
                return false;
            }
            if($validator instanceof CacheValidator){
                if($validator->isCacheStale()){
                    $this->delete($key);
                    return false;
                }
            }
            if($lock && $expires != 0){
                if(time() + BaseCache::CACHE_THRESHOLD >= $expires){
                    $lock = $this->lock($key);
                    if(!$lock){
                        Edge::app()->logger->debug("Could not acquire lock. Serving stale");
                        return $data;
                    }
                    Edge::app()->logger->debug("Acquired lock");
                    //increase the expiration until we calculate the value
                    //so that other threads can serve the old value and
                    //increase concurrency
                    $expires = 5*60;
                    $this->add($key, $data, $expires, $validator);
                    return false;
                }
            }
            return $data;
        }
        return false;
    }

    protected function lock($key){
        $key = $this->getNsKey($key);
        return $this->getLock($key.".lock", BaseCache::LOCK_KEY_TIMEOUT);
    }

    /**
     * Delete the data from the cache
     * @param string $key
     */
    public function delete($key){
        $this->deleteValue($this->getNsKey($key));
    }

    abstract public function setValue($key, $value, $ttl);
    abstract public function getValue($key);
    abstract public function deleteValue($key);
    abstract protected function getLock($key, $ttl);
}