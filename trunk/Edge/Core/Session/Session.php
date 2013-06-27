<?php
namespace Edge\Core\Session;

class Session{

    public function __construct($storage, $settings){
        $driver = new $storage($settings);
        session_set_save_handler($driver, true);
        session_start();
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
        if (isset($_SESSION['acc']) && (time() - $_SESSION['acc'] > $settings['session.timeout'])) {
            $this->destroy();
            $_SESSION = array();
            $_SESSION['initiated'] = true;
        }
        $_SESSION['acc'] = time();
    }

    public function getSessionId(){
        return session_id();
    }

    public function destroy(){
        session_unset();
        session_destroy();
    }

    public function __get($key) {
        if(array_key_exists($key, $_SESSION))
            return $_SESSION[$key];
    }

    public function __isset($key) {
        return array_key_exists($key, $_SESSION);
    }

    public function __set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function __unset($key) {
        unset($_SESSION[$key]);
    }
}