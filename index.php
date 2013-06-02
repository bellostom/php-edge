<?php
//Register class loader
require('Edge/Core/ClassLoader.php');
$loader = new ClassLoader();
$loader->registerNamespaces(array(
    'Edge' => __DIR__,
    'Application' => __DIR__
));
$loader->register();

use Edge\Core\Edge;

$webApp = new Edge(__DIR__."/Application/Config/config.php");
$oRouter = new $webApp->routerClass($webApp->getRoutes());
$oRouter->invoke();