<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 24/5/2013
 * Time: 11:09 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Session;

class SessionFileStorage extends BaseSessionStorage{
    private $sessionDir;

    public function __construct(array $settings){
        parent::__construct($settings);
        $this->sessionDir = $settings['session.path'];
        if(!is_dir($this->sessionDir)){
            mkdir($this->sessionDir, 0777);
        }
    }

    public function open($savePath, $sessionName){
        return true;
    }

    public function close(){
        return true;
    }

    public function read($id){
        return (string)@file_get_contents("$this->sessionDir/sess_$id");
    }

    public function write($id, $data){
        return file_put_contents("$this->sessionDir/sess_$id", $data) === false ? false : true;
    }

    public function destroy($id){
        $file = "$this->sessionDir/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
    }

    public function gc($maxlifetime){
        foreach (glob("$this->sessionDir/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }
}