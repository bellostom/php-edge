<?php
namespace Edge\Controllers;

use Edge\Core\Interfaces;
use Edge\Core;

abstract class BaseController implements Interfaces\ACLControl{
	public static $css = array();
	public static $js = array();

    protected $response;
    protected $_components = array();

    public function __construct(Core\Response $response){
        $this->response = $response;
    }

    /**
     * Return the requested service
     * @param $name
     * @return mixed
     */
    public function __get($name){
        return $this->_components[$name];
    }

	public function on_request(){}
	public function get_login_url(){}
    public function preProcess(){}
    public function postProcess(){}

    protected function dependencies(){
        return array();
    }

	/**
	 * Interface implementations
	 * Post process filter which replaces all
	 * internationalization strings with
	 * the appropriate literals
	 * @see Edge/core/Multilingual::i18n()
	 */
	public function translate()	{
		$settings = Core\Settings::getInstance();
		$context = Core\Context::getInstance();
		$body = $context->response->body;
		$lng = (isset($context->session->lang))?$context->session->lang:$settings->default_lang;
		$lang = Language::getLanguageById($lng);
		$lang = (array) $lang->getStrings();
		$translate = array();
        foreach ($lang as $key => $value){
		    $translate['@@' . $key . '@@'] = $value;
        }
        $context->response->body = strtr($body, $translate);
	}

	/**
	 *
	 * Authenticate user
	 * @param $username
	 * @param $password
	 * @param $remember_me
	 */
	protected function authenticate($username, $password,
								    $remember_me=false)	{
		$settings = Settings::getInstance();
		$user = call_user_func($settings->user_class.'::getUserByUsername', $username);
		if(!is_null($user) && $user->authenticate($password)){
			$context = Context\Context::getInstance();
			$context->session->userID = (int) $user->id;
			$context->session->regenerate();
			$context->user = $user;
			if($remember_me)
				UserToken::setCookieToken($user, Utils::genRandom(10));
			return true;
		}
		return false;
	}

	/**
	 *
	 * Generic method to handle 404
	 * http codes.
	 * Should be overwritten by apps
	 * to provide more details
	 * @param string $http_method
	 */
	public function notFound($http_method) {
		return 'Not Found';
	}

	/**
	 *
	 * Generic method to handle 50x
	 * http codes.Should be overwritten by apps
	 * to provide more details
	 * @param string $http_method
	 */
	public function serverError($http_method) {
		return 'Server Error';
	}

	/**
	 *
	 * Logout the user and destroy
	 * the session
	 */
	public function logout() {
		$context = Core\Context::getInstance();
		$context->session->destroy();
		return true;
	}

	/**
	 *
	 * Construct the link for the css
	 * or javascript files. Iterate
	 * through the files, store their modification
	 * time and get the max
	 * @param string $section
	 * @param string $type
	 */
	protected static function getLink($type) {
		$mod = array();
		$arr = static::$$type;
		$arr = array_unique($arr);
		foreach($arr as $style)
			$mod[] = filemtime($style);
		rsort($mod);
		$module = strtolower(get_called_class());
		return "/$module/asset/".$mod[0].".".$type;
	}

	/**
	 *
	 * Combining all javascript and css files into
	 * one, minify them, cache them locally
	 * and output an expiration header of 1 year.
	 * This way the browser caches the file and
	 * never makes another call to the server, unless
	 * the filename changes
	 * @param string $file
	 */
	public function asset($file) {
		$class = strtolower(get_called_class());
		list($revision, $type) = explode('.', $file);
		$revision = (int)$revision;
		$file = sprintf("%s.%s", $class, $type);
		$cache = new Core\Cache($file);
		$arr = static::$$type;
		$arr = array_unique($arr);
		if($cache->isValid($revision)){
			$content = $cache->load();
		}else{
			$content = '';
			foreach($arr as $style){
				$content .= file_get_contents($style)."\n";
			}
			if($type == 'js') {
				$content = JSMin::minify($content);
			}else{
				$content = cssmin::minify($content);
			}
			$cache->cache($content);
		}
		$context = Context\Context::getInstance();
		Context::$isStaticContent = true;
		if($type == 'js') {
			$context->response->contentType = 'text/javascript';
		}else{
			$context->response->contentType = 'text/css';
		}
		$context->response->expires(time() + 365 * 24 * 3600);
		$context->response->setEtag(md5_file($cache->cache_file), filemtime($cache->cache_file));
		return $content;
	}
}