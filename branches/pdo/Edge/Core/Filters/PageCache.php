<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Class PageCache
 * Filter that handles page caching
 * @package Edge\Core\Filters
 */
class PageCache extends BaseFilter{

    use \Edge\Core\TraitCachable;

    private $isCached = false;

    public function __construct(array $attrs){
        parent::__construct(array_key_exists('applyTo', $attrs)?$attrs['applyTo']:array("*"));
        $this->init($attrs);
    }

    /**
     * Check if there is a valid cached item and if so
     * send it directly to the browser
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function preProcess(Http\Response $response, Http\Request $request){
        $val = $this->get($lock=true);
        if($val){
            $response->body = $val;
            $this->isCached = true;
            //Edge::app()->logger->debug("Loading from cache page ".$request->getRequestUrl());
            return false;
        }
        return true;
    }

    /**
     * After the request has been processed, get the response
     * body and cache it
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function postProcess(Http\Response $response, Http\Request $request){
        if(!$this->isCached){
            Edge::app()->logger->debug("Creating page cache for ". $request->getRequestUrl());
            $this->set($response->body);
        }
        return true;
    }
}