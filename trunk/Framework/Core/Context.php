<?php
namespace Framework\Core;
use Framework\Core\Session;

class Context extends Singleton {
	public $response;
	public $session;
	public $user;
	public $router = null;
	public $loadedFromCache = false;
	public $isStaticContent = false;
	public $autoCommit = false;

	protected function __construct() {
		$settings = Settings::getInstance();
		$this->session = Session\Session::getInstance();
		$this->response = Response::getInstance();
		//$this->user = call_user_func($settings->user_class.'::getUserById', $this->session->userID);
	}
}
?>