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
var_dump(WebApp::instance()->get('user'));
exit;
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