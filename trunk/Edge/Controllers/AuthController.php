<?php
/**
 *
 * Class to implement access control to restricted
 * areas. Extend this class for all servlets that
 * need authorization check.
 * @author thomas
 *
 */
namespace Edge\Controllers;

class AuthController extends BaseController{

	/**
	 * Access control implementation.
	 * Http requests on admin modules undergo
	 * access control, to ensure that unauthorized
	 * or under privileged users cannot call these
	 * methods
	 */
	public function on_request(){
		$context = Context::getInstance();
		if($context->user->id == User::GUEST){
			$proto = 'http';
			if($_SERVER['SERVER_PORT'] == '443')
				$proto = 'https';
			$url = sprintf("%s://%s%s", $proto, $_SERVER['SERVER_NAME'],
										$_SERVER['REQUEST_URI']);
			$context->session->redirect = $url;
			$context->response->redirect($this->get_login_url());
		}
	}
}
?>