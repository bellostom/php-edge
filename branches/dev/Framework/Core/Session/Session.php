<?php
namespace Framework\Core\Session;
use Framework\Core\Singleton;
use Framework\Models\User;

class Session extends Singleton {
	CONST IS_IP = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/';
	public $sessionID;

	protected function __construct() {
		session_start();
		if (!isset($_SESSION['initiated'])) {
		    session_regenerate_id();
		    $_SESSION['initiated'] = true;
		}
		$this->sessionID = session_id();
		if(!isset($this->userID)){
			$this->userID = User::GUEST;
		}
	}

	public function set_cookie($name, $value, $expire=0) {
		$host = explode('.', $_SERVER['SERVER_NAME']);
		$len = count($host);
		$host = '.'.$host[$len-2].'.'.$host[$len-1];
		setcookie($name, $value, $expire, '/', $host);
	}

	public function regenerate() {
		session_regenerate_id(true);
		$this->sessionID = session_id();
	}

	public function destroy() {
		if(count($_COOKIE) > 0){
			foreach($_COOKIE as $key=>$value){
				if($key == 'frmauth'){
					$val = explode('_', $_COOKIE['frmauth']);
					$token = UserToken::getBySidAndToken($val[0], $val[1]);
					if($token)
						$token->delete();
				}
				setcookie($key, false, time()-10000, '/', '.'.$_SERVER['SERVER_NAME']);
				setcookie($key, false, time()-10000, '/', $_SERVER['SERVER_NAME']);
			}
		}
		return session_destroy();
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
?>