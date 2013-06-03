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
    private $config = array();
    public $router;

    public function __construct($config){
        $defaults = include(__DIR__.'/../Config/config.php');
        if(is_string($config)){
            $config = include($config);
        }
        $config = array_replace_recursive($defaults, $config);
        if($config['env'] == 'development'){
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);
        }else{
            ini_set('display_errors', 'Off');
        }
        date_default_timezone_set($config['timezone']);
        $this->container = new Pimple();
        $this->registerServices($config['services']);
        $this->routes = $config['routes'];
        unset($config['services'], $config['routes']);
        $this->config = $config;
        self::$__instance = $this;
    }

    /**
     * Register the services specified in the config
     * file. These services reside in an IoC object
     * and are not initialized until they are invoked
     * @param array $services
     */
    protected function registerServices(array $services){
        foreach($services as $name=>$params){
            if(is_array($params) && array_key_exists('invokable', $params)){
                $shared = array_key_exists('shared', $params)?$params['shared']:false;
                if(array_key_exists('invokable', $params) && is_callable($params['invokable'])){
                    $value = $params['invokable'];
                }else{
                    $value = function($c) use ($params){
                        $class = new \ReflectionClass($params['invokable']);
                        return $class->newInstanceArgs($params['args']);
                    };
                }
            }
            else{
                $shared = false;
                $value = $params;
            }
            if($shared){
                $this->container[$name] = $this->container->share($value);
            }else{
                $this->container[$name] = $value;
            }
        }
    }

    public function getConfig($name){
        return $this->config[$name];
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