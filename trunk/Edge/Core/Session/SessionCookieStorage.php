<?php
namespace Edge\Core\Session;

use Edge\Core\Edge,
    Edge\Core\Http\EncryptedCookie;

/**
 * Class SessionCookieStorage
 * Use a signed and encrypted cookie for storing cookie data
 * @package Edge\Core\Session
 */
class SessionCookieStorage extends BaseSessionStorage{
    private $cookie;

    public function __construct(EncryptedCookie $cookie){
        ob_start();
        $this->cookie = $cookie;
    }

    public function read($id){
        $data = $this->cookie->get($id);
        if($data === false){
            return '';
        }
        return $data;
    }

    public function write($id, $data){
        $this->cookie->set($id, $data, 0);
        ob_end_flush();
    }

    public function destroy($id){
        $this->cookie->delete($id);
        return true;
    }

    public function gc($maxlifetime){
        return true;
    }
}