<?php
namespace Edge\Core\Cache;

use Edge\Core\Edge;

class OutputCache implements \Edge\Core\Interfaces\Filter{
    private $varyBy;
    private $ttl;
    private $cacheValidator;

    public function __construct($varyBy, $ttl, $cacheValidator=null){
        $this->varyBy = $varyBy;
        $this->ttl = $ttl;
        $this->cacheValidator = $cacheValidator;
    }

    private function getCacheKey(){
        switch($this->varyBy){
            case 'url':
                $request = Edge::app()->request;
                $key = md5(serialize(array(
                    $request->getRequestUrl(),
                    $request->getParams()
                )));
                break;
        }
        return $key;
    }

    public function preProcess(){

    }

    public function postProcess(){
        $body = Edge::app()->response->body;
        Edge::app()->cache->add($this->getCacheKey(), $body, $this->ttl, $this->cacheValidator);
    }
}