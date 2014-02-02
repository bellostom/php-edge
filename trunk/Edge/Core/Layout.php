<?php
namespace Edge\Core;

use Edge\Utils\JSMin,
    Edge\Utils\Css;
/**
 * Class Layout
 * Manages the layout template file and provides
 * helper functions to handle minification and
 * compression of static css and javascript files
 * @package Edge\Core
 */
class Layout extends Template{

	protected $js;
    protected $jsFiles = [];
    protected $css;
    protected $cssFiles = [];

    /**
     * @param $tpl
     * @param array $js
     * @param array $css
     */
    public function __construct($tpl, array $js, array $css){
		$this->js = $js;
        $this->css = $css;
        parent::__construct($tpl);
	}

    public function getJsScript(){
        return $this->getLink('js', $this->jsFiles);
    }

    public function getCssScript(){
        return $this->getLink('css', $this->cssFiles);
    }

    /**
     * Loop through the files and get the max
     * modification date which is then used as part
     * of the url we send out. When any of the files
     * is modified, the mod date changes and this will
     * invalidate the cached version of the browser
     * @param $type (js|css)
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
            if($type == 'js') {
                $content = JSMin::minify($content);
            }else{
                $content = Css::minify($content);
            }
            Edge::app()->cache->add($key, $content);
        }
    }
}