<?php
set_include_path(realpath(__DIR__."/../../"));
spl_autoload_extensions('.php');
spl_autoload_register(function($class){
    if(substr($class, 0, 7) == 'Monolog'){
        $class = 'Edge\Core\Logger\\'.$class;
    }
    $file = str_replace('\\','/',$class);
    require "{$file}.php";
});