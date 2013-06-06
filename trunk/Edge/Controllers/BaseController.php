<?php
namespace Edge\Controllers;

use Edge\Core,
    Edge\Core\Interfaces;

abstract class BaseController{
	public static $css = array();
	public static $js = array();

    protected $response;
    private $_components = array();

    public function __construct(Core\Http\Response $response){
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

    /**
     * Define filters to be run before and after the request
     * has been processed. Filters must extend
     * Edge\Core\Filters\BaseFilter class
     * and implement 2 methods
     * preProcess($response, $request) and
     * postProcess($response, $request)
     *
     * Here we define a Post Process filter that replaces
     * content within caches, that has been declared as
     * dynamic
     *
     * Example usage from a child class
     * return array_merge(parent::__filters(), array(
        array(
            'Edge\Core\Filters\PageCache',
            'ttl' => 10*60,
            'varyBy' => 'url',
            'cacheValidator' => new Validator\QueryValidator("SELECT COUNT(id) FROM users"),
            'applyTo' => array('index')
            )
        ));
     * @return array
     */
    public function __filters(){
        return array(
            array('Edge\Core\Filters\DynamicOutput')
        );
    }

    public function __dependencies(){
        return array();
    }

    public function __setDependency($name, $service){
        $this->_components[$name] = $service;
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