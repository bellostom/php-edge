<?php
namespace Edge\Core\Queue;

require __DIR__."/Resque.php";

class Queue extends \Resque{

    public function __construct($server){
        $this->autoload();
        Queue::setBackend($server);
    }

    private function autoload(){
        spl_autoload_register(function($class){
            $file = "../Edge/Core/Queue/".str_replace('_','/',$class).".php";
            if(is_file($file)){
                include $file;
            }
        });
    }

    public function add($queue, $class, $args = null, $trackStatus = false){
        return static::enqueue($queue, $class, $args, $trackStatus);
    }

}