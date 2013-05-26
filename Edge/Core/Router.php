<?php
namespace Edge\Core;

class Router{
	protected $class;
	protected $method;
	protected $args = array();
	protected $oReflection;
	protected $oReflectionMethod;
	protected $instance;
	protected $id;
    protected $response;

	const RPC_MATCH = '/jsonrpc.+[\'"]method[\'"]\s*:\s*[\'"](.*?)[\'"]/';

	public function __construct(){
        $this->response = new Response();
		try{
			$this->setAttrs();
		}catch(Exception $e){
			Logger::log($e->getMessage());
            print $e->getMessage();
			$response = Response::getInstance();
			$response->httpCode = 500;
			$response->write();
		}
	}

	public function getArgs(){
		return $this->args;
	}

	public function getMethod(){
		return $this->method;
	}

	protected function handleServerError(){
		$context = Context::getInstance();
		$settings = Settings::getInstance();
		$arg = strtolower($_SERVER['REQUEST_METHOD']);
		$class = new $settings->server_error[0]();
		$this->oReflection = new ReflectionClass($settings->server_error[0]);
		$this->instance = $this->oReflection->newInstance();
		$context->response->httpCode = 500;
		$context->response->body = call_user_func(array($class, $settings->server_error[1]), $arg);
	}

	protected function handle404Error(){
		$context = Context::getInstance();
		$settings = Settings::getInstance();
		$arg = strtolower($_SERVER['REQUEST_METHOD']);
		$class = new $settings->not_found[0]();
		$this->oReflection = new \ReflectionClass($settings->not_found[0]);
		$this->instance = $this->oReflection->newInstance();
		$context->response->httpCode = 404;
		$context->response->body = call_user_func(array($class, $settings->not_found[1]), $arg);
	}

	protected static function populateFiles(){
		$search_for = '/_name|_tmp_name|_size|_type/';
		foreach($_POST as $key=>$value) {
			if(preg_match($search_for, $key)){
				$field = explode('_', $key, 2);
				$_FILES[$field[0]][$field[1]] = $value;
			}
		}
	}

	protected function setAttrs(){
		//$context = Context::getInstance();
        $url = '';
        if(array_key_exists('PATH_INFO', $_SERVER)){
		    $url = $_SERVER['PATH_INFO'];
        }
		//$settings = Settings::getInstance();
		if(empty($url)){
			$url = $settings->default_url;
		}
		if ($url[strlen($url)-1] == '/'){
			$url = substr($url, 0, -1);
		}
		$url = substr($url, 1);
		$url = explode("/", $url);

		$url = array_map('htmlspecialchars', $url);
		$this->class = ucfirst(array_shift($url));
		if(count($url) > 0) {
			$this->method = array_shift($url);
			if(count($url) > 0){
				$this->args = $url;
			}
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(strstr($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') ||
				strstr($_SERVER['CONTENT_TYPE'] , 'multipart/form-data')){
				$this->args = array(&$_POST);
				if(strstr($_SERVER['CONTENT_TYPE'] , 'multipart/form-data')){
					static::populateFiles();
				}
			}
			else if(array_key_exists('CONTENT_TYPE', $_SERVER) &&
					strstr($_SERVER['CONTENT_TYPE'], 'application/json')){
				$this->response->contentType = 'application/json';
				$request = file_get_contents("php://input");

				if(!preg_match(Router::RPC_MATCH, $request)){
					throw new \ReflectionException('Server supports the jsonrpc 2.0 protocol');
				}
				$ob = json_decode($request, true);
				$this->method = $ob['method'];
				$this->args = $ob['params'];
				$this->id = $ob['id'];
			}
			else{
				throw new \ReflectionException('Unknown content type for POST method');
			}
		}else{
			if(is_null($this->method)){
				$this->method = $settings->default_method;
			}
		}
	}

	private function passMethodArgs(){
		return $this->oReflectionMethod->getNumberOfParameters() > 0;
	}

	private function checkAuth(){
		$methods = array('on_request');
		foreach($methods as $meth){
			$method = $this->oReflection->getMethod($meth);
			$method->invoke($this->instance);
		}
	}

	private function postProcess(){
		$oInstance = $this->oReflection;
		$ints = $oInstance->getInterfaces();
		if (count($ints) > 0)
		{
			foreach ($ints as $oInt){
				if ($oInt->getName() == 'PostProcessFilter' ||
					$oInt->isSubclassOf(new \ReflectionClass('Edge\Core\Interfaces\PostProcessFilter'))) {
					foreach($oInt->getMethods() as $method)	{
						$oRefMethod = $oInstance->getMethod($method->getName());
						$oRefMethod->invoke($this->instance);
					}
				}
			}
		}
	}

	private function preProcess(){
		$oInstance = $this->oReflection;
		if (count($oInstance->getInterfaces()) > 0) {
			foreach ($oInstance->getInterfaces() as $oInt) {
				if ($oInt->getName() == 'PreProcessFilter' ||
					$oInt->isSubclassOf(new \ReflectionClass('Edge\Core\Interfaces\PreProcessFilter'))) {
					foreach($oInt->getMethods() as $method)	{
						$oRefMethod = $oInstance->getMethod($method->getName());
						$oRefMethod->invoke($this->instance);
					}
				}
			}
		}
	}

	protected function invokeRequest(){
		if ($this->passMethodArgs()){
			$resp = $this->oReflectionMethod->invokeArgs($this->instance,
														 $this->args);
		}
		else{
			$resp = $this->oReflectionMethod->invoke($this->instance);
		}
		return $resp;
	}

	protected function handleJsonResponse(){
		if(array_key_exists('CONTENT_TYPE', $_SERVER) &&
						strstr($_SERVER['CONTENT_TYPE'], 'application/json')){
			$payload = array(
				'jsonrpc' => '2.0',
				'result' => $this->response->body,
				'id' => $this->id
			);
            $this->response->body = json_encode($payload);
		}
	}

    /**
     * Inject the dependencies specified by the Controller class
     * Invoke the dependencies method and assign the requested
     * services.
     * @param \Edge\Controllers\BaseController $instance
     */
    protected static function setDependencies(\Edge\Controllers\BaseController $instance){
        $reflection = new \ReflectionClass($instance);
        $method = $reflection->getMethod('dependencies');
        $method->setAccessible(true);
        $deps = $method->invoke($instance);
        if(count($deps) > 0){
            $webApp = Edge::app();
            $prop = new \ReflectionProperty($instance, '_components');
            $prop->setAccessible(true);
            $val = array();
            foreach($deps as $service){
                $val[$service] = $webApp->{$service};
            }
            $prop->setValue($instance, $val);
        }
    }

    public function invoke(){
        $class = sprintf('Application\Controllers\%s', $this->class);
        $instance = new $class($this->response);
        static::setDependencies($instance);
        $instance->on_request();
        if(method_exists($instance, $this->method)){
            try{
                $processed = false;
                $retries = 0;
                $max_retries = 20;

                while(!$processed && ($retries < $max_retries)) {
                    try{
                        $retries++;
                        $instance->preProcess();
                        /*if(!$context->loadedFromCache){
                            $context->response->body = $this->invokeRequest();
                            $this->handleJsonResponse();
                        }*/
                        $this->response->body = call_user_func_array(array($instance, $this->method), $this->args);
                        $instance->postProcess();
                        /*if($context->autoCommit){
                            $_db = Database\MysqlMaster::getInstance();
                            $_db->commit();
                        }*/
                        $processed = true;
                    }catch(DeadLockException $e) {
                        Logger::log('RETRYING');
                        usleep(100);
                    }
                }
                if(!$processed) {
                    Logger::log('DEADLOCK ERROR');
                    throw new Exception('Deadlock detected');
                }
            }
            catch(UnauthorizedException $e){
                $this->response->httpCode = 401;
            }
            catch(\ReflectionException $e){
                Logger::log($e->getMessage());
                $this->handle404Error();
            }
            catch(\Exception $e){
                Logger::log($e->getMessage());
                $this->handleServerError();
            }
        }
        $this->response->write();
    }
}