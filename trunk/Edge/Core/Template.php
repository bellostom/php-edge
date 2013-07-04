<?php
namespace Edge\Core;

use Edge\Core\Edge;
/**
 * Class InternalCache
 * Uses the TraitCachable trait to expose caching
 * to the classes that use it.
 * @package Edge\Core
 */
class InternalCache{

    use TraitCachable;

    public function __construct(array $cacheAttrs=array()){
        $this->init($cacheAttrs);
    }
}

/**
 * Class Template
 * Manages a template file.
 * All parameters passed to the instance by the controller
 * are stored in an array and are made directly available to the
 * template file via $name.
 * Additionally, the $this keyword is made available to the file
 * and refers to the Template instance, so that you can invoke
 * methods directly.
 * You can define caching attributes for the whole template
 * via $cacheAttrs or you can initiate fragment caching from
 * within the template file.
 * @package Edge\Core
 */
class Template extends InternalCache{

	protected $tpl;
    protected $attrs = array();
    protected $isCachable = false;
    private $fragmentCache;

	public function __construct($tpl, array $cacheAttrs=array()){
		$this->tpl = $tpl;
        $this->attrs['this'] = $this;
        if(isset(Edge::app()['i18n'])){
            $this->attrs['i18n'] = Edge::app()->i18n;
        }
        parent::__construct($cacheAttrs);
	}

    protected function init(array $cacheAttrs){
        parent::init($cacheAttrs);
        if(count($cacheAttrs) > 0){
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
	    	throw new \Exception("Template $this->tpl does not exist");
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
     * Escape string coming from unknown sources
     * to prevent XSS
     * @param $str
     * @return string
     */
    public function escape($str){
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * There are cases that little bits of information within a cache
     * are dynamic, but since these are small pieces we do not need
     * to create a different cached version for each of these data.
     * Instead we call this method passing it a callback that retrieves
     * the dynamic content. The actual callback invokation is handled
     * by the Edge\Core\Filters\DynamicOutput filter that runs
     * on post process
     * Example usage from within the template file
     *
     * <?php $this->alwaysEvaluate("\Application\Controllers\Home::fetchUser"); ?>
     *
     * The callback should be a string pointing to a static method, that can be invoked
     * with call_user_func()
     * @param $callback
     */
    public function alwaysEvaluate($callback){
        if(!is_string($callback)){
            throw new \Exception("Callback must be a string");
        }
        echo sprintf('{{%s}}', $callback);
    }

    /**
     * Initialize fragment caching, only if the template itself
     * is not set to be cached.
     * If the cached content exists, echo it
     * Example usage
       <?php if($this->startCache("myid")): ?>
        <div><?= time(); ?></div>
        <?php $this->endCache(); ?>
       <?php endif; ?>
     * @params string $key The cache key
     * @param array $cacheAttrs
     * @return bool
     */
    public function startCache($key, array $cacheAttrs=array()){
        if($this->isCachable){
            throw new \Exception("The template is set to be cached. Disable fragment caching");
        }
        $cacheAttrs['key'] = $key;
        $this->fragmentCache = new InternalCache($cacheAttrs);
        $content = $this->fragmentCache->get();
        if($content){
            echo $content;
            return false;
        }
        ob_start();
        ob_implicit_flush(false);
        return true;
    }

    /**
     * End fragment caching.
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