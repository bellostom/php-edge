<?php
namespace Edge\Core;

use Edge\Core\Database\MysqlMaster,
    Edge\Core\Exceptions\NotFound;

class Router{
	protected $controller;
	protected $method;
	protected $args = array();
    protected $response;
    protected $request;
    protected $routes;
    protected $permissions = null;

	public function __construct(array $routes){
        $this->routes = $routes;
        $this->response = Edge::app()->response;
        $this->request = Edge::app()->request;
        register_shutdown_function(array($this, 'onApplicationShutdown'));
        Edge::app()->router = $this;
		try{
			$this->setAttrs();
		}catch(Exception $e){
            $msg = $e->getMessage();
            Edge::app()->logger->err($msg);
			$this->handleServerError($msg);
			$this->response->write();
		}
	}

	public function getArgs(){
		return $this->args;
	}

    public function getPermissions(){
        return $this->permissions;
    }

	public function getAction(){
		return $this->method;
	}

    public function getController(){
        return $this->controller;
    }

    public function onApplicationShutdown(){
        $error = error_get_last();
        $fatal = ($error && in_array($error['type'], [E_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR]));
        if ($fatal){
            $this->handleServerError($error["message"]);
            Edge::app()->response->write();
        }
    }

    /**
     * Return the URL from the routes array that corresponds
     * to the selected attributes
     * ie
     * parent::createLink(get_called_class(), 'updateRole', [':id' => $role->id], 'POST');
     * or
     * parent::createLink('Application\Controllers\Home', 'index', ["anchor"=>"#list_products"])
     * @param $controller
     * @param $action
     * @param array $attrs
     * @param string $method
     * @return string
     */
    public function createLink($controller, $action, array $attrs=array(), $method='GET'){
        $routes = $this->routes;
        if(!isset($routes[$method])){
            return null;
        }
        foreach($routes[$method] as $url=>$options){
            if($options[0] == $controller && $options[1] == $action){
                $anchor = '';
                if(isset($attrs['anchor'])){
                    $anchor = $attrs['anchor'];
                    unset($attrs['anchor']);
                }
                if(substr($url, strlen($url) - 1) == "*"){
                    //handle case such as /css/:file/*
                    if(strstr($url, ":") !== false){
                        $url = substr($url, 0, strlen($url) - 2);
                    }
                    else{
                        //handle case such as /cms/page/create/*
                        $url = substr($url, 0, strlen($url) - 1);
                        return $url . join("/", array_values($attrs));
                    }
                }
                $keys = array_keys($attrs);
                $vals = array_values($attrs);
                return str_replace($keys, $vals, $url).$anchor;
            }
        }
        if($method != '*'){
            return $this->createLink($controller, $action, $attrs, '*');
        }
        return null;
    }

	protected function handleServerError($msg){
        $edge = Edge::app();
		$class = $edge->getConfig('serverError');
        $this->controller = new $class[0];
        $this->method = $class[1];
        if($this->response->httpCode == 200){
            $this->response->httpCode = 500;
        }
		$this->response->body = call_user_func(array($this->controller, $this->method), $msg);
	}

	protected function handle404Error($message = "" ){
        $edge = Edge::app();
        $class = $edge->getConfig('notFound');
        $this->controller = new $class[0];
        $this->method = $class[1];
        $this->response->httpCode = 404;
        $this->response->body = call_user_func(array($this->controller, $this->method), $this->request->getRequestUrl(), $message);
	}

    /**
     * Try to map a URL to a Controller=>action array
     * Routes are defined as
     *
     * 'GET' => array(
            '/' => array("Home", "index"),
            '/page/action/:name/:id' => array("Home", "index"),
            '/user/view/1' => array("User", "display")
            '/user/edit/:id' => array("User", "edit"),
            '/user/load/*' => array("User", "load"),
            '/user/display/:id/*' => array("User", "show")
        ),
        'POST' => array(
            '/rest/api/:id' => array('Home', 'post')
        ),
        '*' => array(
            '/api/update/:id' => array("Home", "test")
        )
     *
     * Try to find an exact match of the url
     * within the array's keys. This is the most quick and efficient way
     * to resolve routes so try to abide to this approach as much as possible.
     *
     * To define routes that match partially (ie /user/view/:id)
     * just add some named parameter convention to the url. Note however,
     * that although the notation is one of named parameters what will end
     * up as argument in the action will be the actual value and not an array.
     *
     * In order for the function to match a partial route it does the following
     * 1. counts the dashes in the url
     * 2. initiates the loop and if the dashes in the current url
     *    are not equal with the url's, it skips the rule
     * 3. Then it tries to match the partial url by splitting the route url to ":"
     * 4. It compares the url to the requested url, after it strips out the named params
     * 5. If there is a match, we found the route and the action's arguments
     *
     *
     *
     * @param $url
     * @param $routes
     * @return array|bool
     */
    private function uriResolver($url, $routes){
        if(isset($routes[$url])){
            $ret = $routes[$url];
            $ret[] = array();
            return $ret;
        }

        foreach($routes as $requestedUrl => $attrs){
            $urlDashes = substr_count($url, "/");
            $greedy = false;
            $partial = false;
            $extraArgs = false;
            //if route is defined to match anything ie /home/article/*
            if(substr($requestedUrl, strlen($requestedUrl)-1) == "*"){
                $requestedUrl = substr($requestedUrl, 0, strlen($requestedUrl)-2);
                $greedy = true;
                //if we also have a partial match as well
                //is /home/article/:/id/*
                if(substr_count($requestedUrl, ":") > 0){
                    $partial = true;
                }
            }

            if($greedy){
                if(substr_count($url, "?") > 0){
                    $url = explode("?", $url)[0];
                }
                if(!$partial){
                    if(substr($url, 0, strlen($requestedUrl)) === $requestedUrl){
                        $args = explode("/", substr($url, strlen($requestedUrl), strlen($url)));
                        unset($args[0]);
                        $attrs[] = array_map('htmlspecialchars', $args);
                        return $attrs;
                    }
                }
                else{
                    //decrement url dashed in order to match
                    //the partial URL the rest of the code
                    $partialParts = explode("/", $requestedUrl);
                    $urlParts = explode("/", $url);
                    $extraArgs = array_slice($urlParts, count($partialParts));
                    $urlDashes -= count($extraArgs);
                }
            }

            if(substr_count($requestedUrl, "/") != $urlDashes){
                continue;
            }
            $parts = explode(":", $requestedUrl);

            if(count($parts) > 1){
                $urlToMatch = substr($parts[0], 0, -1);
                if(strncmp ($url, $urlToMatch, strlen($urlToMatch)) === 0){
                    $args = explode("/", substr($url, strlen($urlToMatch), strlen($url)));
                    unset($args[0]);
                    $attrs[] = array_map('htmlspecialchars', $args);
                    return $attrs;
                }
            }
        }
        return false;
    }

    /**
     * Map URI to Controller and Action based on the
     * http method
     * @param $uri
     * @return array|bool
     */
    protected function resolveRoute($uri){
        $httpMethod = Edge::app()->request->getHttpMethod();
        $route = false;
        if(array_key_exists($httpMethod, $this->routes)){
            $routes = $this->routes[$httpMethod];
            $route = $this->uriResolver($uri, $routes);
        }
        if(!$route && isset($this->routes['*'])){
            $route = $this->uriResolver($uri, $this->routes['*']);
        }
        return $route;
    }

	protected function setAttrs(){
		$url = $_SERVER['REQUEST_URI'];
		if(empty($url)){
			$url = "//";
            $_SERVER['REQUEST_URI'] = "/";
		}
		if ($url != '/' && $url[strlen($url)-1] == '/'){
			$url = substr($url, 0, -1);
		}
        $route = $this->resolveRoute($url);
        if(!$route){
            Edge::app()->logger->err("$url is not mapped to any route");
            $this->handle404Error();
            $this->response->write();
        }

		$this->controller = ucfirst($route[0]);
        $this->method = $route[1];
        $this->args = array_values($route[2]);
        if(isset($route['acl'])){
            $this->permissions = $route['acl'];
            unset($route['acl']);
        }

        if(!$this->request->is('get')){
            $extraArgs = $this->request->getParams();
            if($extraArgs){
                $this->args[] = $extraArgs;
            }
            if($this->request->isJsonRpc()){
                $this->method = $this->request->getTransformer()->method;
            }
        }
	}

    protected static function getFilters(\Edge\Controllers\BaseController $instance){
        $filters = $instance->filters();
        if(count($filters) > 0){
            $filterInstances = array();
            foreach($filters as $filter){
                $class = array_shift($filter);
                if(count($filter) > 0){
                    $instance = new $class($filter);
                }
                else{
                    $instance = new $class;
                }
                $filterInstances[] = $instance;
            }
            return $filterInstances;
        }
        return $filters;
    }

    /**
     * Execute the filters
     * Iterate each one and invoke the filter
     * If any of the filters returns false, we stop
     * the execution and return.
     * @param array $filters
     * @param $method (preProcess | postProcess)
     */
    private function runFilters(array $filters, $method){
        foreach($filters as $filter){
            if($filter->appliesTo($this->method)){
                $val = $filter->{$method}($this->response, $this->request);
                if($val === false){
                    return false;
                }
            }
        }
        return true;
    }

    public function invoke(){
        if(strstr($this->controller, "\\")){
            $class = $this->controller;
        }
        else{
            $class = sprintf('Application\Controllers\%s', $this->controller);
        }

        $this->controller = new $class();
        if(method_exists($this->controller, $this->method)){
            try{
                $filters = static::getFilters($this->controller);
                $invokeRequest = $this->runFilters($filters, 'preProcess');
                if($invokeRequest){
                    $processed = false;
                    $retries = 0;
                    $max_retries = 20;

                    while(!$processed && ($retries < $max_retries)) {
                        try{
                            $retries++;
                            $this->response->body = $this->request
                                                          ->getTransformer()
                                                          ->encode(call_user_func_array(array($this->controller,
                                                                                                $this->method),
                                                                                        $this->args));
                            $processed = true;
                        }catch(Exceptions\DeadLockException $e) {
                            Edge::app()->logger->info('RETRYING TRANSACTION');
                            usleep(100);
                        }
                    }
                    if(!$processed) {
                        Edge::app()->logger->err('DEADLOCK ERROR');
                        throw new \Exception('Deadlock detected');
                    }
                }
                $this->runFilters($filters, 'postProcess');
            }
            catch(\Exception $e){
                $db = Edge::app()->db;
                if($db instanceof MysqlMaster){
                    $db->rollback();
                }
                Edge::app()->logger->err($e->getMessage());
                if($e instanceof NotFound){
                    $this->handle404Error($e->getMessage());
                }
                else{
                    $this->handleServerError($e->getMessage());
                }
            }
        }
        else{
            $this->handle404Error();
        }
        $this->response->write();
    }
}