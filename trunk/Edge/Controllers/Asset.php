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
        $etag = md5($key);
        $mod = explode("_", $key);
        $mod = $mod[0];
        $response = Edge::app()->response;
        $response->contentType = $contentType;
        $response->expires($mod + 365 * 24 * 3600);
        $response->setEtag($etag);
        $response->lastModified($mod);
        if($response->isEtagValid($etag, $mod)){
            $response->httpCode = 304;
            $response->write();
        }
        return Edge::app()->cache->get($key);
    }
}