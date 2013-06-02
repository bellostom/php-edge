<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 2/6/2013
 * Time: 9:36 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core;

use Edge\Core\Http;


trait TraitCachable {
    protected $varyBy;
    protected $ttl;
    protected $cacheValidator;

    public function init(array $attrs){
        $this->varyBy = $attrs['varyBy'];
        $this->ttl = $attrs['ttl'];
        $this->cacheValidator = array_key_exists('cacheValidator', $attrs)?$attrs['cacheValidator']:null;
    }

    /**
     * Return a key that is specific to the
     * underlying class
     * @return string
     */
    protected function getExtraParams(){
        return "";
    }

    /**
     * Get a unique key to be used for the cached item
     * @param $request
     * @return null|string
     */
    private function getCacheKey(){
        static $key;
        if($key === null){
            $request = Edge::app()->request;
            $router = Edge::app()->router;
            $defaults = array(
                $request->getMethod(),
                $router->getController(),
                $router->getAction(),
                $this->getExtraParams()
            );
            switch($this->varyBy){
                case 'url':
                    $defaults[] = $request->getParams();
                    break;
                case 'session':
                    $defaults[] = Edge::app()->session->getSessionId();
                    break;
            }
            $key = md5(serialize($defaults));
        }
        return $key;
    }

    /**
     * Retrieve the value from the cache
     * @return mixed
     */
    protected function get(){
        $key = $this->getCacheKey();
        return Edge::app()->cache->get($key);
    }

    /**
     * Write the value to the cache
     * @param $value
     */
    protected function set($value){
        Edge::app()->cache->add($this->getCacheKey(), $value, $this->ttl, $this->cacheValidator);
    }
}