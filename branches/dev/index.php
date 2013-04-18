<?php
//Register class loader
require('Framework/Core/ClassLoader.php');
$loader = new ClassLoader();
$loader->registerNamespaces(array(
    'Framework' => __DIR__,
    'Application' => __DIR__
));
$loader->register();

use Framework\Core\Router;
use Framework\Core;

//Load our application's configuration
$config = Core\Configuration::getInstance();
$config->register(array(
    'name'=> 'Application',
    'config' => __DIR__."/Application/Config/config.php"
));

//init bootstrap
$oRouter = new Router();
$oRouter->invoke();
?>