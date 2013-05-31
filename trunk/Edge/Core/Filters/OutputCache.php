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

    private $varyBy;
    private $ttl;
    private $cacheValidator;

    public function __construct(array $attrs){
        parent::__construct(array_key_exists('applyTo', $attrs)?$attrs['applyTo']:array("*"));
        $this->varyBy = $attrs['varyBy'];
        $this->ttl = $attrs['ttl'];
        $this->cacheValidator = array_key_exists('cacheValidator', $attrs)?$attrs['cacheValidator']:null;
    }

    /**
     * Get a unique key to be used for the cached item
     * @param $request
     * @return null|string
     */
    private function getCacheKey($request){
        static $key;
        if($key === null){
            switch($this->varyBy){
                case 'url':
                    $key = md5(serialize(array(
                        $request->getRequestUrl(),
                        $request->getParams()
                    )));
                break;
                case 'session':
                    $key = md5($request->getRequestUrl().Edge::app()->session->getSessionId());
                    break;
            }
        }
        return $key;
    }

    /**
     * Check if there is a valid cached item and if so
     * send it directly to the browser
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function preProcess(Http\Response $response, Http\Request $request){
        $key = $this->getCacheKey($request);
        $val = Edge::app()->cache->get($key);
        if($val){
            $response->body = $val;
            $response->write();
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
        Edge::app()->cache->add($this->getCacheKey($request), $body, $this->ttl, $this->cacheValidator);
    }
}