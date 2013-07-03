<?php
namespace Edge\Core\Session;

use Edge\Core\Interfaces\SessionStorageInterface;

abstract class BaseSessionStorage implements \SessionHandlerInterface{

    public function __construct(array $settings){
        ini_set('session.name', $settings['session.name']);
        ini_set('session.cookie_httponly', $settings['session.httponly']);
        ini_set('session.gc_maxlifetime', $settings['session.timeout']);
    }
}