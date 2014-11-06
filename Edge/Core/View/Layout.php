<?php
namespace Edge\Core\View;

use Edge\Utils\StaticBundler;
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
     * @param $tpl
     * @param array $js
     * @param array $css
     */
    public function __construct($tpl, array $js, array $css, $minify=true){
        static::addJs($js);
        static::addCss($css);
        $this->minify = $minify;
        parent::__construct($tpl);
	}

    protected function initBundler(){
        $this->bundler = new StaticBundler(static::$js, static::$css, $this->minify);
    }

    /**
     * Add a script tag with javascript code
     * This will be added in the head of the html
     * @param $script
     */
    public static function addInlineJs($script){
        static::$inlineJs[] = $script;
    }

    /**
     * Add a inline style
     * This will be added in the head of the html
     * @param $script
     */
    public static function addInlineCss($script){
        static::$inlineCss[] = $script;
    }

    /**
     * Add javascript files to the array
     * @param array $files
     */
    public static function addJs(array $files){
        static::$js = array_merge($files, static::$js);
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
            $content = StaticBundler::minify($type, $content);
        }
        return $content;
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