<?php
//print substr("Thomas bellos", 2, strlen("Thomas bellos"));
//exit;
//Register class loader
require('Edge/Core/ClassLoader.php');
$loader = new ClassLoader();
$loader->registerNamespaces(array(
    'Edge' => __DIR__,
    'Application' => __DIR__
));
$loader->register();

ini_set('display_errors', 'On');
error_reporting(E_ALL);

use Edge\Core\Edge,
    Edge\Core\Router;

$webApp = new Edge(__DIR__."/Application/Config/config.php");
$oRouter = new Router($webApp->getRoutes());
$oRouter->invoke();