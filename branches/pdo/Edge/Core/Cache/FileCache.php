<?php
namespace Edge\Core\Cache;

use Edge\Core\Exceptions\EdgeException;

class FileCache extends BaseCache{
	private $cache_dir;

	public function __construct($cacheDir){
		$this->cache_dir = $cacheDir;
		if(!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);
		}
	}

    private function getCacheFile($key){
        $f = preg_replace("/\//","_",$key);
        return sprintf("%s/%s.cache", $this->cache_dir, $f);
    }

	public static function clearCache(){
		Cache::removeFromCacheByPattern('*');
	}

	public static function removeFromCacheByPattern($pattern){
		$settings = Settings::getInstance();
		$cache_dir = $settings->cache_dir;
		chdir($cache_dir);
		if ($pattern != '*') {
			$pattern = sprintf("*%s*", $pattern);
		}
		$res = glob($pattern);
		foreach($res as $file) {
			unlink($cache_dir ."/". $file);
		}
	}

    public function deleteValue($key){
        unlink($this->getCacheFile($key));
    }

    public function setValue($key, $value, $ttl){
        if($ttl <= 0){
            $ttl = 31536000;
        }
        $ttl += time();

        $cacheFile = $this->getCacheFile($key);
        if(@file_put_contents($cacheFile, $value, LOCK_EX) !== false){
            @chmod($cacheFile, 0777);
            return touch($cacheFile, $ttl);
        }
        return false;
	}

	public function getValue($key){
        $cacheFile = $this->getCacheFile($key);
		if(file_exists($cacheFile)){
            if(filemtime($cacheFile) < time()){
                unlink($cacheFile);
                return false;
            }
			return file_get_contents($cacheFile);
		}
		return false;
	}

    protected function getLock($key, $ttl){
        throw new EdgeException("Do not use this engine for locking");
    }
}