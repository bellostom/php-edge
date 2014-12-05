<?php
namespace Edge\Utils;

use Edge\Core\Edge;

/**
 * Class StaticBundler
 * This class is responsible for minifying and
 * caching static css and js files
 * @package Edge\Utils
 */
class StaticBundler {
    protected $jsFiles = [];
    protected $cssFiles = [];
    protected $js = [];
    protected $css = [];
    protected $minify;

    /**
     * @param array $js
     * @param array $css
     */
    public function __construct(array $js, array $css, $minify=true){
        $this->js = $js;
        $this->css = $css;
        $this->minify = $minify;
    }

    public function getJsScript(){
        return $this->getLink('js', $this->jsFiles);
    }

    public function getCssScript(){
        return $this->getLink('css', $this->cssFiles);
    }

    public function setMinify($value){
        $this->minify = $value;
    }

    /**
     * Loop through the files and get the max
     * modification date which is then used as part
     * of the url we send out. When any of the files
     * is modified, the mod date changes and this will
     * invalidate the cached version of the browser
     * @param $type (js|css)
     * @param $memo array Store any files specified in dir format
     * @return string
     */
    protected function getLink($type, array &$memo) {
        $arr = array_unique($this->$type);
        foreach($arr as $file){
            if(substr($file, -1) == '*'){
                $files = glob($file);
                foreach($files as $fname){
                    $memo[$fname] = filemtime($fname);
                }
            }
            else{
                $memo[$file] = filemtime($file);
            }
        }
        $modified = (string) max(array_values($memo));
        $key = md5($modified . serialize($memo));
        $file = sprintf("%s_%s.%s", $modified, $key, $type);
        $link = Edge::app()->router->createLink("Edge\\Controllers\\Asset", $type,
            [':file' => $file]);
        $this->cache($file, $type);
        return $link;
    }

    public static function minify($type, $content){
        if($type == 'js') {
            $content = JSMin::minify($content);
        }else{
            $content = Css::minify($content);
        }
        return $content;
    }

    /**
     * Cache the files in the cache storage
     * @param $key
     * @param $type
     */
    private function cache($key, $type){
        if(!Edge::app()->cache->get($key)){
            $content = '';
            $valName = sprintf("%sFiles", $type);
            $arr = array_unique(array_keys($this->$valName));
            foreach($arr as $file){
                $content .= file_get_contents($file)."\n";
            }
            if($this->minify){
                static::minify($type, $content);
            }
            Edge::app()->cache->add($key, $content);
        }
    }
} 