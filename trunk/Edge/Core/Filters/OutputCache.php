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
class OutputCache implements \Edge\Core\Interfaces\Filter{

    private $varyBy;
    private $ttl;
    private $cacheValidator;

    public function __construct(array $attrs){
        $this->varyBy = $attrs['varyBy'];
        $this->ttl = $attrs['ttl'];
        $this->cacheValidator = array_key_exists('cacheValidator', $attrs)?$attrs['cacheValidator']:null;
    }

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
            }
        }
        return $key;
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        $key = $this->getCacheKey($request);
        $val = Edge::app()->cache->get($key);
        if($val){
            $response->body = $val;
            $response->write();
        }
    }

    public function postProcess(Http\Response $response, Http\Request $request){
        $body = $response->body;
        Edge::app()->cache->add($this->getCacheKey($request), $body, $this->ttl, $this->cacheValidator);
    }
}