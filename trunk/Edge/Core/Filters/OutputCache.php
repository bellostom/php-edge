<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Class OutputCache
 * Filter that handles page caching
 * It supports variation based on
 * url: the cache key is composed based on the request params
 *
 * ttl: the time until the cache expires
 * cacheValidator: optional instance of Edge\Core\Cache\Validator\CacheValidator
 * @package Edge\Core\Filters
 */
class OutputCache extends BaseFilter{

    use \Edge\Core\TraitCachable;

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
        $val = $this->get();
        if($val){
            $response->body = $val;
            return false;
        }
    }

    /**
     * After the request has been processed, get the response
     * body and cache it
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function postProcess(Http\Response $response, Http\Request $request){
        $body = $response->body;
        $this->set($body);
    }
}