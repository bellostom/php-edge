<?php

namespace Edge\Core\Session;

class SessionMemcacheStorage extends BaseSessionStorage{

    protected $link;

    public function __construct(\Edge\Core\Cache\MemoryCache $link){
        $this->link = $link;
    }

    public function read($id){
        return $this->link->getValue($id);
    }

    public function write($id, $data){
        return $this->link->setValue($id, $data);
    }

    public function destroy($id){
        $this->link->deleteValue($id);
        return true;
    }

    public function gc($maxlifetime){
        return true;
    }
}