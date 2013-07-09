<?php
require('../Edge/Core/Loader.php');
use Edge\Core\Edge;
$webApp = new Edge("Application/Config/config.php");
$router = $webApp->getConfig('routerClass');
$oRouter = new $router($webApp->getRoutes());
$oRouter->invoke();