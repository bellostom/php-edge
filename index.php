<?php
//Register class loader
require('Framework/Core/ClassLoader.php');
$loader = new ClassLoader();
$loader->registerNamespaces(array(
    'Framework' => __DIR__,
    'Application' => __DIR__
));
$loader->register();

use Framework\Core\WebApp,
    Framework\Core\Router;

$webApp = new WebApp(__DIR__."/Application/Config/config.php");
/*var_dump(WebApp::instance()->db);
var_dump(WebApp::instance()->writedb);
var_dump(WebApp::instance()->db);
$webApp->logger->log('test');
exit;*/
//Load our application's configuration
/*$config = new Core\Configuration();
$config->register(array(
    'name'=> 'Application',
    'config' => __DIR__."/Application/Config/config.php"
));*/

//init bootstrap
$oRouter = new Router();
$oRouter->invoke();
?>