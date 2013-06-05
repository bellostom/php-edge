<?php
namespace Edge\Core;

use Edge\Core\Http;

trait TraitCachable {

    protected $varyBy;
    protected $ttl;
    protected $cacheValidator;
    protected $key = false;
    private static $defaults = array(
        'varyBy' => 'url',
        'ttl' => 0,
        'cacheValidator' => null,
        'key' => false
    );

    public function init(array $attrs){
        $attrs = array_merge(self::$defaults, $attrs);
        $this->varyBy = $attrs['varyBy'];
        $this->ttl = $attrs['ttl'];
        $this->key = $attrs['key'];
        $this->cacheValidator = $attrs['cacheValidator'];
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
        if($this->key){
            return $this->key;
        }
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
            Edge::app()->logger->debug($defaults);
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