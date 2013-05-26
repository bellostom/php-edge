<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 24/5/2013
 * Time: 11:09 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Session;
use Edge\Core\Interfaces\SessionStorageInterface;

abstract class BaseSessionStorage implements \SessionHandlerInterface{

    public function __construct(array $settings){
        ini_set('session.name', $settings['session.name']);
        ini_set('session.gc_maxlifetime', $settings['session.timeout']);
    }
}