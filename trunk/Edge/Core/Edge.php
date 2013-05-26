<?php
namespace Edge\Core;
use Edge\Core\Pimple;

/**
 * Class responsible for loading configurations options and
 * bootstrapping the application
 */
class Edge{

    private $container;
    private static $__instance;
    private $routes;

    public function __construct($config){
        if(is_string($config)){
            $config = include($config);
        }
        $this->container = new Pimple();
        $this->registerServices($config['services']);
        date_default_timezone_set($config['timezone']);
        $this->routes = $config['routes'];
        self::$__instance = $this;
    }

    protected function registerServices(array $services){
        foreach($services as $name=>$params){
            $shared = array_key_exists('shared', $params)?$params['shared']:false;
            if(is_callable($params['invokable'])){
                $closure = $params['invokable'];
            }else{
                if(array_key_exists('type', $params)){
                    $shared = false;
                    $closure = $params['invokable'];
                }
                else{
                    $closure = function($c) use ($params){
                        $class = new \ReflectionClass($params['invokable']);
                        return $class->newInstanceArgs($params['args']);
                    };
                }
            }
            if($shared){
                $this->container[$name] = $this->container->share($closure);
            }else{
                $this->container[$name] = $closure;
            }
        }
    }

    public function __get($service){
        return $this->container[$service];
    }

    public function getRoutes(){
        return $this->routes;
    }

    public static function app(){
        return self::$__instance;
    }
}