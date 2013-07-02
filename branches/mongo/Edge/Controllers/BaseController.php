<?php
namespace Edge\Controllers;

use Edge\Core,
    Edge\Core\Layout,
    Edge\Core\Interfaces;

abstract class BaseController{

	protected static $css = array();
	protected static $js = array();
    protected static $layout = null;

    /**
     * Render the layout file
     * @param Core\Template $tpl
     * @param array $attrs
     * @return mixed|string
     */
    protected static function render(Core\Template $tpl, $attrs=array()){
        if(!static::$layout){
            throw new Core\Exceptions\EdgeException("Layout template must be defined by class ". get_called_class());
        }
        $layout = new Layout(static::$layout, static::$js, static::$css);
        $layout->title = "Edge PHP framework";
        $layout->body = $tpl->parse();
        return $layout->parse();
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
     * return array_merge(parent::filters(), array(
        array(
            'Edge\Core\Filters\PageCache',
            'ttl' => 10*60,
            'varyBy' => 'url',
            'cacheValidator' => new Validator\QueryValidator("SELECT COUNT(id) FROM users"),
            'applyTo' => array('index')
         ),
         array('Edge\Core\Filters\DynamicOutput')
        ));
     * @return array
     */
    public function filters(){
        return array();
    }

    /**
     * Load the specified template file
     * @param $file
     * @param array $cacheAttrs
     * @return Core\Template
     */
    protected static function loadView($file, $cacheAttrs=array()){
        return new Core\Template($file, $cacheAttrs);
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

    public function authorize($username, $password){
        $userClass = Core\Edge::app()->getConfig('userClass');
        $user = $userClass::getUserByUsername($username);
        if($user && $user->authenticate($password)){
            Core\Edge::app()->user($user);
            return true;
        }
        return false;
    }

	/**
	 *
	 * Logout the user and destroy
	 * the session
	 */
	public function logout() {
        $app = Core\Edge::app();
		$app->session->destroy();
        $app->user(\Edge\Models\User::getUserById(\Edge\Models\User::GUEST));
		return true;
	}
}