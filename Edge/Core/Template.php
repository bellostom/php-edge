<?php
namespace Edge\Core;

use Edge\Core\Exceptions\NotFound;

class InternalCache{
    use TraitCachable;

    public function __construct(array $cacheAttrs=array()){
        $this->init($cacheAttrs);
    }
}

class Template extends InternalCache{

	private $tpl;
	private $attrs = array();
    private $isCachable = false;
    private $fragmentCache;

	public function __construct($tpl, array $cacheAttrs=array()){
		$this->tpl = $tpl;
        $this->attrs['this'] = $this;
        parent::__construct($cacheAttrs);
	}

    protected function __init(array $cacheAttrs){
        if(count($cacheAttrs) > 0){
            $this->init($cacheAttrs);
            $this->isCachable = true;
        }
    }

	public function __set($member, $value){
		$this->attrs[$member] = $value;
	}

	public function __get($key){
		return $this->attrs[$key];
	}

	public function parse(){
	    if(!file_exists($this->tpl)){
	    	throw new NotFound("Template $this->tpl does not exist");
	    }
        if($this->isCachable){
            $val = $this->get();
            if($val){
                return $val;
            }
            else{
                $val = $this->readTemplate();
                $this->set($val);
                return $val;
            }
        }
	    return $this->readTemplate();
	}

    /**
     * Initialize fragment caching
     * If the cached content exists, echo it
     * @param array $cacheAttrs
     * @return bool
     */
    public function startCache(array $cacheAttrs){
        $this->fragmentCache = new InternalCache($cacheAttrs);
        $content = $this->cache->get();
        if($content){
            echo $content;
            return false;
        }
        ob_start();
        ob_implicit_flush(false);
        return true;
    }

    /**
     * End fragment caching
     * Get buffer contents, cache it and echo it
     */
    public function endCache(){
        $content = ob_get_clean();
        $this->fragmentCache->set($content);
        echo $content;
    }

    protected function getExtraParams(){
        return $this->tpl;
    }

	private function readTemplate(){
		extract($this->attrs);
	    ob_start();
	    if (is_file($this->tpl)){
	        include($this->tpl);
        }
	    $parsed = ob_get_contents();
	    ob_end_clean();
	    return $parsed;
	}
}
?>