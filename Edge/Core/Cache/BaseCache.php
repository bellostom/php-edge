<?php
namespace Edge\Core\Cache;
use Edge\Core\Cache\Validator\CacheValidator;

/**
 * Class BaseCache
 * Base class which handles caching.
 *
 * @package Edge\Core\Cache
 */
abstract class BaseCache {

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

    /**
     * @param string $key The unique hey for the cached item
     * @param mixed $value The cache value
     * @param int $ttl How long the item will be cached. 0 for infinite cache. Defaults to 0
     * @param CacheValidator $cacheValidator Instance of CacheValidator (optional)
     * @return boolean
     */
    public function add($key, $value, $ttl=0, $cacheValidator=null){
        if(!is_null($cacheValidator)){
            $cacheValidator->execute();
        }
        $value = static::serialize(array($value, $cacheValidator));
        return $this->setValue($key, $value, $ttl);
    }

    /**
     * Get the cached value from the underlying storage
     * @param string $key
     * @return mixed
     */
    public function get($key){
        $value = $this->getValue($key);
        if($value){
            $value = static::unserialize($value);
            if($value[1] instanceof CacheValidator){
                if($value[1]->isCacheStale()){
                    $this->delete($key);
                    return false;
                }
            }
            return $value[0];
        }
        return false;
    }

    /**
     * Delete the data from the cache
     * @param string $key
     */
    public function delete($key){
        $this->deleteValue($key);
    }

    abstract public function setValue($key, $value, $ttl);
    abstract public function getValue($key);
    abstract public function deleteValue($key);
}