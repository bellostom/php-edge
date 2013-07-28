<?php
namespace Edge\Core\Session;

class Session{

    public function __construct(\SessionHandlerInterface $storage, $settings){
        ini_set('session.name', $settings['session.name']);
        ini_set('session.cookie_httponly', $settings['session.httponly']);
        ini_set('session.gc_maxlifetime', $settings['session.timeout']);

        session_set_save_handler($storage, true);
        session_start();

        if (!isset($_SESSION['initiated'])) {
            $this->regenerate();
        }
        if (isset($_SESSION['acc']) && (time() - $_SESSION['acc'] > $settings['session.timeout'])) {
            $this->destroy();
            $_SESSION = array();
            $_SESSION['initiated'] = true;
        }
        $_SESSION['acc'] = time();
    }

    public function regenerate(){
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
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