<?php
namespace Framework\Core\Cache;

class Cache{
	public $cache_file;
	protected $cache_dir;

	public function __construct($tpl){
		$settings = Settings::getInstance();
		$this->cache_dir = $settings->cache_dir;
		$f = preg_replace("/\//","_",$tpl);
		if(!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);
		}
		$this->cache_file = sprintf("%s/%s.cache", $this->cache_dir, $f);
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

	public function isValid($ctime){
		if(file_exists($this->cache_file)){
			if($ctime > filemtime($this->cache_file)){
				unlink($this->cache_file);
				return false;
			}
			return true;
		}
		return false;
	}

	public function cache($content, $callback=null){
		$lock = new McacheMutex(md5($this->cache_file).".lock");
		$acquired = $lock->get_lock($callback);
		if($acquired['data']) {
			return;
		}
		$w = fopen($this->cache_file, "wb");
		fwrite($w, $content);
		fclose($w);
		$lock->release_lock();
	}

	public function load(){
		if(file_exists($this->cache_file)){
			return file_get_contents($this->cache_file);
		}
		return false;
	}
}
?>