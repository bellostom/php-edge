<?php
namespace Edge\Core;

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
        Edge::app()->router = $this;
		try{
			$this->setAttrs();
		}catch(Exception $e){
            Edge::app()->logger->err($e->getMessage());
			$response = Response::getInstance();
			$response->httpCode = 500;
			$response->write();
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

	protected function handleServerError($msg){
        $edge = Edge::app();
		$class = $edge->getConfig('serverError');
		$this->response->body = call_user_func(array(new $class[0], $class[1]), $msg);
	}

	protected function handle404Error(){
        $edge = Edge::app();
        $class = $edge->getConfig('notFound');
        $this->response->httpCode = 404;
        $this->response->body = call_user_func(array(new $class[0], $class[1]), $this->request->getRequestUrl());
	}

    /**
     * Try to map a URL to a Controller=>action array
     * Routes are defined as
     *
     * 'GET' => array(
            '/' => array("Home", "index"),
            '/page/action/:name/:id' => array("Home", "index"),
            '/user/view/:id' => array("User", "display")
            '/user/view/1' => array("User", "action")
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

        $urlDashes = substr_count($url, "/");
        foreach($routes as $requestedUrl => $attrs){
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
        $url = '';
        if(array_key_exists('PATH_INFO', $_SERVER)){
		    $url = $_SERVER['PATH_INFO'];
        }

		if(empty($url)){
			$url = "//";
            $_SERVER['PATH_INFO'] = "/";
		}
		if ($url[strlen($url)-1] == '/'){
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
        $this->args = $route[2];
        if(isset($route['acl'])){
            $this->permissions = $route['acl'];
            unset($route['acl']);
        }

        if(!$this->request->is('get')){
            $this->args[] = $this->request->getParams();
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

        $instance = new $class();
        if(method_exists($instance, $this->method)){
            try{
                $filters = static::getFilters($instance);
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
                                                          ->encode(call_user_func_array(array($instance, $this->method),
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
                Edge::app()->logger->err($e->getMessage());
                if($this->response->httpCode == 200){
                    $this->response->httpCode = 500;
                }
                $this->handleServerError($e->getMessage());
            }
        }
        else{
            $this->handle404Error();
        }
        $this->response->write();
    }
}