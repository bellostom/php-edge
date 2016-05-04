<?php
set_include_path("." . PATH_SEPARATOR . realpath(__DIR__."/../../"));
spl_autoload_extensions('.php');
spl_autoload_register(function($class){
    $file = str_replace('\\','/',$class);
    if(is_file("../{$file}.php")){
        include "../{$file}.php";
    }
});
/*$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once($composerAutoload);
}*/