<?php
//Register class loader
require('Edge/Core/ClassLoader.php');
$loader = new ClassLoader();
$loader->registerNamespaces(array(
    'Edge' => __DIR__,
    'Application' => __DIR__,
    'Monolog' => __DIR__."/Edge/Core/Logger"
));
$loader->register();
use Edge\Core\Edge;

$webApp = new Edge(__DIR__."/Application/Config/config.php");
$router = $webApp->getConfig('routerClass');
$oRouter = new $router($webApp->getRoutes());
$oRouter->invoke();