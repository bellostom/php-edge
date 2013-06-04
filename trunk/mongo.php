<?php
ini_set("display_errors","On");
require("Edge/Core/Database/MongoConnection.php");

$settings = array(
    "db" => "users"
);
$m = new MongoConnection($settings);

$cursor = $m->find("user", array(), array("username" => 1));
print $cursor->count();
foreach($cursor as $data){
    print $data["username"];
}
exit;
$m = new MongoClient(); // connect
$db = $m->selectDB("users");
var_dump($db);