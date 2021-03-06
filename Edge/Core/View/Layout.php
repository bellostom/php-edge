<?php
namespace Edge\Core\View;

use Edge\Core\Edge;

/**
 * Class Layout
 * Manages the layout template file
 * Template instances can also add js and css files to the array
 *
 * @package Edge\Core\View
 */
class Layout extends BaseTemplate{

	protected static $js = [];
    protected static $css = [];
    protected static $inlineJs = [];
    protected static $inlineCss = [];
    protected $minify;
    protected $bundler;

    /**
     *
     * @param $tpl
     * @param array $js
     * @param array $css
     */
    public function __construct($tpl, array $js, array $css, $minify=true){
        //We do not call addJs/addCss here, as we want the files
        //coming from the controllers to be loaded first
        static::$js = array_merge($js, static::$js);
        static::$css = array_merge($css, static::$css);
        $this->minify = $minify;
        parent::__construct($tpl);
	}

    protected function initBundler(){
        $cls = Edge::app()->getConfig("staticBundler");
        $this->bundler = new $cls(static::$js, static::$css, $this->minify);
    }

    /**
     * Add a script tag with javascript code
     * This will be added in the head of the html
     * @param $script
     */
    public static function addInlineJs($script){
        $key = md5($script);
        if(!isset(static::$inlineJs[$key])){
            static::$inlineJs[$key] = $script;
        }
    }

    /**
     * Add a inline style
     * This will be added in the head of the html
     * @param $css
     */
    public static function addInlineCss($css){
        $key = md5($css);
        if(!isset(static::$inlineCss[$key])){
            static::$inlineCss[$key] = $css;
        }
    }

    /**
     * Add javascript files to the array
     * @param array $files
     */
    public static function addJs(array $files){
        static::$js = array_merge(static::$js, $files);
    }

    /**
     * Add css files to the array
     * @param array $files
     */
    public static function addCss(array $files){
        static::$css = array_merge($files, static::$css);
    }

    /**
     * Implode any inline javascript fragments
     * @return string
     */
    public function getInlineJs(){
        $content = implode("\n", static::$inlineJs);
        return $this->getInlineFragment($content, "js");
    }

    /**
     * Implode any inline css fragments
     * @return string
     */
    public function getInlineCss(){
        $content = implode("\n", static::$inlineCss);
        return $this->getInlineFragment($content, "css");
    }

    protected function getInlineFragment($content, $type){
        if($this->minify){
            $cls = Edge::app()->getConfig("staticBundler");
            $content = $cls::minify($type, $content);
        }
        return $content;
    }

    public function getJsFiles(){
        static::$js = array_values(array_unique(static::$js));
        return static::$js;
    }

    public function getCssFiles(){
        static::$css = array_values(array_unique(static::$css));
        return static::$css;
    }

    public function getJsScript(){
        if(!$this->bundler){
            $this->initBundler();
        }
        return $this->bundler->getJsScript();
    }

    public function getCssScript(){
        if(!$this->bundler){
            $this->initBundler();
        }
        return $this->bundler->getCssScript();
    }

    public function setMinify($value){
        $this->minify = $value;
    }
}