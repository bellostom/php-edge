<?php
namespace Edge\Core\Session;

use Edge\Core\Interfaces\SessionStorageInterface;

abstract class BaseSessionStorage implements \SessionHandlerInterface{

    public function open($savePath, $sessionName){
        return true;
    }

    public function close(){
        return true;
    }
}