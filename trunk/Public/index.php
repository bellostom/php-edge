<?php
//Register class loader
require('../Edge/Core/ClassLoader.php');
$loader = new ClassLoader();
$loader->registerNamespaces(array(
    'Application' => $loader->basePath
));
$loader->register();
use Edge\Core\Edge;

$webApp = new Edge($loader->basePath."/Application/Config/config.php");
$router = $webApp->getConfig('routerClass');
$oRouter = new $router($webApp->getRoutes());
$oRouter->invoke();