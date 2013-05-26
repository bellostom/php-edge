<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 24/5/2013
 * Time: 10:51 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Session;


class Session{
    private $driver;

    public function __construct($storage, $settings){
        $this->driver = new $storage($settings);
        session_set_save_handler($this->driver, true);
        session_start();
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $settings['session.timeout'])) {
            /*$params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );*/
            session_unset();     // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
            //session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
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