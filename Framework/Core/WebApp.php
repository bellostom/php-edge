<?php
namespace Framework\Core;
use Framework\Core\Pimple;

/**
 * Class responsible for loading configurations options
 */
class WebApp{

    private $container;
    private static $__instance;

    /**
    * Load framework configuration file by default.
    */
    public function __construct($config){
        if(is_string($config)){
            $config = include($config);
        }
        $this->container = new Pimple();
        $this->registerServices($config['services']);
        self::$__instance = $this;
    }

    protected function registerServices(array $services){
        foreach($services as $name=>$params){
            if(is_callable($params)){
                $closure = $params;
                unset($params);
            }else{
                $closure = function($c) use ($params){
                    $class = new \ReflectionClass($params['class']);
                    return $class->newInstanceArgs($params['args']);
                };
            }
            if(isset($params) && isset($params['shared']) && $params['shared']){
                $this->container[$name] = $this->container->share($closure);
            }else{
                $this->container[$name] = $closure;
            }
        }
    }

    public function get($service){
        return $this->container[$service];
    }

    public static function instance(){
        return self::$__instance;
    }
}
?>