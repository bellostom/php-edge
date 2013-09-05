<?php
set_include_path("." . PATH_SEPARATOR . realpath(__DIR__."/../../"));
spl_autoload_extensions('.php');
spl_autoload_register(function($class){
    $file = str_replace('\\','/',$class);
    if(is_file("../{$file}.php")){
        include "../{$file}.php";
    }
});