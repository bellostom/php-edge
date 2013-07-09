<?php
namespace Edge\Core\Session;

class SessionFileStorage extends BaseSessionStorage{
    private $sessionDir;

    public function __construct($sessionDir){
        $this->sessionDir = $sessionDir;
        if(!is_dir($this->sessionDir)){
            mkdir($this->sessionDir, 0777);
        }
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