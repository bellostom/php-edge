<?php
namespace Edge\Core\View;

use Edge\Core\TraitCachable,
    Edge\Core\Edge;

/**
 * Class InternalCache
 * Uses the TraitCachable trait to expose caching
 * to the classes that use it.
 * @package Edge\Core
 */
class InternalCache extends BaseTemplate{

    use TraitCachable;

    public function __construct($tpl, array $cacheAttrs=array()){
        parent::__construct($tpl);
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

    protected $isCachable = false;
    private $fragmentCache;

    protected function init(array $cacheAttrs){
        parent::init($cacheAttrs);
        if(count($cacheAttrs) > 0){
            $this->isCachable = true;
        }
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

    public function getCsrfToken(){
        return sprintf('<input name="csrfToken" type="hidden" value="%s" />',
                        Edge::app()->request->getCsrfToken());
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
        static::startOutputBuffering();
        return true;
    }

    protected static function startOutputBuffering(){
        ob_start();
        ob_implicit_flush(false);
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

    public function addJsFiles(array $files){
        if(!Edge::app()->request->isAjax()){
            Layout::addJs($files);
        }
        else{
            $ret = [];
            foreach($files as $file){
                $ret[] = sprintf('<script src="/%s"></script>', $file);
            }
            return implode("\n", $ret);
        }
    }

    public function addCssFiles(array $files){
        Layout::addCss($files);
    }

    public function startInlineJs(){
        if(!Edge::app()->request->isAjax()){
            static::startOutputBuffering();
        }
    }

    public function endInLineJs(){
        if(!Edge::app()->request->isAjax()){
            $content = ob_get_clean();
            Layout::addInlineJs($content);
        }
    }

    public function startInlineCss(){
        if(!Edge::app()->request->isAjax()){
            static::startOutputBuffering();
        }
    }

    public function endInLineCss(){
        if(!Edge::app()->request->isAjax()){
            $content = ob_get_clean();
            Layout::addInlineCss($content);
        }
    }

    protected function getExtraParams(){
        return $this->tpl;
    }
}