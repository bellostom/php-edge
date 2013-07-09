<?php

namespace Edge\Core\Session;

class SessionRedisStorage extends SessionMemcacheStorage{

    public function __construct(\Edge\Core\Cache\RedisCache $link){
        $this->link = $link;
    }
}