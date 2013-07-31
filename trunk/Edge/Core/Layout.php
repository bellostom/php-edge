<?php
namespace Edge\Core;

use Edge\Core\Edge,
    Edge\Utils\JSMin,
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
    protected $css;

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
        return $this->getLink('js');
    }

    public function getCssScript(){
        return $this->getLink('css');
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
    protected function getLink($type) {
        $mod = array();
        $arr = array_unique($this->$type);
        foreach($arr as $file){
            $mod[] = filemtime($file);
        }
        $modified = (string) max($mod);
        $key = md5($modified . serialize($arr));
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
            $arr = array_unique($this->$type);
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