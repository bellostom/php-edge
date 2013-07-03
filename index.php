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

use Application\Models\Human,
    Application\Models\Child,
    Application\Models\Article,
    Application\Models\Feed;

$child = Child::getItemById('51d4184db1a43d360872b0dd');
var_dump($child->human);
exit;
$human = Human::getItemById('51d4184db1a43d360872b0df');
var_dump($human->childs);
foreach($human->childs as $child){
    echo $child->name;
}
exit;
$options = array();
$options['_id'] = array(
    '$in' => array(new \MongoId('51d4184db1a43d360872b0de'))
);
$r = $webApp->mongo->find("child", $options);
//print_r($options);
//var_dump($r->count());

//exit;
$r = Child::select()
    ->where('_id')
    ->in($human->children)
    ->fetchMode(1)
    ->run();
var_dump($r);
exit;




$art = Article::getItemById(1);
print $art->feed->name;

$f = Feed::getItemById(2);
var_dump($f->articles);
exit;

$f = new Feed();
$f->name="BBC";
$f->save();

$art = new Article();
$art->name = "Article Greece";
$art->feed_id = $f->id;
$art->save();

$art = new Article();
$art->name = "Article Greece1";
$art->feed_id = $f->id;
$art->save();

exit;

$children = array();

$child = new Child();
$child->name = "thomas";
$child->save();

$children[] = $child->_id;

$child = new Child();
$child->name = "bellos";
$child->save();

$children[] = $child->_id;



$h = new Human();
$h->name="John";
$h->surname="Doe";
$h->age=30;
$h->children = $children;
$h->save();
var_dump($h);
exit;


$r = Application\Models\Human::select()
    ->where(array("age" => array('$gt' => 20)))
    ->orWhere(array("name" => "THOMAS"))
    ->fetchMode(1)
    ->run();



/*$options = array();
$options['$or'] = array(
    array("name" => "THOMAS"),
    array("age" => array('$gt' => 12))
);
$r = $webApp->mongo->find("human", $options);
print_r($options);
var_dump($r->count());
foreach($r as $k){
    //var_dump($k);
}*/


$r = Application\Models\Human::select(array("name"))
    ->where(array("age" => array('$gt' => 10, '$lt' => 40)))
    ->orWhere('name')
    ->in("THOMAS")
    ->order(array("name" => 1))
    ->limit(1)
    ->offset(4)
    ->fetchMode(1)
    ->run();
var_dump($r);
foreach($r as $k){
    var_dump($k);
}
exit;
$r = Application\Models\Human::select()
                ->where(array("age" => array('$gt' => 20)))
                ->andWhere(array("name" => "THOMAS"))
                ->orWhere(array("thomas" => "hellos"))
                ->fetchMode(1)
                ->cache(array("ttl" => 60))
                ->run();

foreach($r as $k){
    var_dump($k);
}
exit;
/*var_dump($webApp->mongo->users->user->find(array(
    "username" => "thomas",
    "name" => "hello"
)));*/


$db = new \MongoClient('mongodb://localhost/test');
var_dump($db->users->test);
exit;
// Get the users collection
$c_things = $db->things->thomas->insert(array("thomas"));
var_dump($c_things);
exit;
$oRouter = new $router($webApp->getRoutes());
$oRouter->invoke();