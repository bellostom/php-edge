<?php
namespace Edge\Core;

class Router{
	protected $controller;
	protected $method;
	protected $args = array();
    protected $response;
    protected $request;
    protected $routes;

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

	public function getAction(){
		return $this->method;
	}

    public function getController(){
        return $this->controller;
    }

	protected function handleServerError(){
        $edge = Edge::app();
		$arg = strtolower($_SERVER['REQUEST_METHOD']);
		$class = new $edge->serverError[0]();
		$this->response->httpCode = 500;
		$this->response->body = call_user_func(array($class, $edge->serverError[1]), $arg);
	}

	protected function handle404Error(){
        $edge = Edge::app();
        $arg = strtolower($_SERVER['REQUEST_METHOD']);
        $class = new $edge->notFound[0]();
        $this->response->httpCode = 404;
        $this->response->body = call_user_func(array($class, $edge->notFound[1]), $arg);
	}

    private static function uriResolver($url, $routes){
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
        $httpMethod = Edge::app()->request->getMethod();;
        $route = false;
        if(array_key_exists($httpMethod, $this->routes)){
            $routes = $this->routes[$httpMethod];
            $route = static::uriResolver($uri, $routes);
        }
        if(!$route){
            $route = static::uriResolver($uri, $this->routes['*']);
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
            echo 'Not Found';
            exit;
        }

		$this->controller = ucfirst($route[0]);
        $this->method = $route[1];
        $this->args = $route[2];

        if(!$this->request->is('get')){
            $this->args[] = $this->request->getParams();
            if($this->request->isJsonRpc()){
                $this->method = $this->request->getTransformer()->method;
            }
        }
	}

    /**
     * Inject the dependencies specified by the Controller class
     * Invoke the dependencies method and assign the requested
     * services.
     * @param \Edge\Controllers\BaseController $instance
     */
    protected static function getFilters(\Edge\Controllers\BaseController $instance){
        $filters = $instance->__filters();
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
     * Inject the dependencies specified by the Controller class
     * Invoke the dependencies method and assign the requested
     * services.
     * @param \Edge\Controllers\BaseController $instance
     */
    protected static function setDependencies(\Edge\Controllers\BaseController $instance){
        $deps = $instance->__dependencies();
        if(count($deps) > 0){
            $webApp = Edge::app();
            foreach($deps as $service){
                $instance->__setDependency($service, $webApp->{$service});
            }
        }
    }

    /**
     * Execute the filters
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
        $class = sprintf('Application\Controllers\%s', $this->controller);
        $instance = new $class($this->response);
        static::setDependencies($instance);
        if(method_exists($instance, $this->method)){
            $filters = static::getFilters($instance);
            $invokeRequest = $this->runFilters($filters, 'preProcess');
            if($invokeRequest){
                try{
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
                            /*if($context->autoCommit){
                                $_db = Database\MysqlMaster::getInstance();
                                $_db->commit();
                            }*/
                            $processed = true;
                        }catch(Exceptions\DeadLockException $e) {
                            Edge::app()->logger->info('RETRYING');
                            usleep(100);
                        }
                    }
                    if(!$processed) {
                        Edge::app()->logger->err('DEADLOCK ERROR');
                        throw new \Exception('Deadlock detected');
                    }
                }
                catch(UnauthorizedException $e){
                    $this->response->httpCode = 401;
                }
                catch(\ReflectionException $e){
                    Edge::app()->logger->err($e->getMessage());
                    $this->handle404Error();
                }
                catch(\Exception $e){
                    Edge::app()->logger->err($e->getMessage());
                    $this->handleServerError();
                }
            }
        }
        $this->runFilters($filters, 'postProcess');
        $this->response->write();
    }
}