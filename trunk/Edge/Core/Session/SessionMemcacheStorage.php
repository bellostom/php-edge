<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 24/5/2013
 * Time: 11:09 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Session;

class SessionMemcacheStorage extends BaseSessionStorage{
    private $link;

    public function __construct(array $settings){
        parent::__construct($settings);
        $this->link = $settings['link'];
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