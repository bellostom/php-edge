<?php
namespace Edge\Tests\Core\Session;

use Edge\Core\Session\SessionFileStorage;

class FileSessionTest extends SessionTestCase{

    protected static $dir = '/tmp/edgeCache';

    protected function getSessionEngine(){
        return new SessionFileStorage(static::$dir);
    }
}