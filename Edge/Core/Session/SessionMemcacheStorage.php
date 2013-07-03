<?php

namespace Edge\Core\Session;

class SessionMemcacheStorage extends BaseSessionStorage{
    private $link;

    public function __construct(array $settings){
        parent::__construct($settings);
        $this->link = $settings['session.path'];
    }

    public function open($savePath, $sessionName){
        return true;
    }

    public function close(){
        return true;
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