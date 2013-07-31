<?php
namespace Edge\Controllers;

use Edge\Core\Edge;

/**
 * Class Asset
 * Serves static css and js files to the browser
 * Uses the requested URL to retrieve the item from the cache
 * and outputs it to the browser with aggressive caching (1 year)
 * @package Edge\Controllers
 */
class Asset extends BaseController{

    /**
     * Override any filters to speed up
     * execution
     * @return array
     */
    public function filters(){
        return array();
    }

    public function js($file){
        return static::load($file, "application/javascript");
    }

    public function css($file){
        return static::load($file, "text/css");
    }

    /**
     * Serve the file to the browser with aggressive caching
     * directives.
     * @param $key
     * @param $contentType
     * @return mixed
     */
    protected static function load($key, $contentType){
        $mod = explode("_", $key);
        $mod = $mod[0];
        $response = Edge::app()->response;
        $response->contentType = $contentType;
        $response->expires(time() + 365 * 24 * 3600);
        $response->lastModified($mod);
        return Edge::app()->cache->get($key);
    }
}