<?php
/*$dbh = new PDO('mysql:host=localhost;dbname=edge', 'root', '');

$select = $dbh->query('SELECT * FROM users');
$total = $dbh->query('SELECT * FROM users');

echo $total->rowCount();

print_r($total->fetchAll());
exit;
$total_column = $select->columnCount();
var_dump($total_column);

for ($counter = 0; $counter <= $total_column; $counter ++) {
    $meta = $select->getColumnMeta($counter);
    $column[] = $meta['name'];
}

exit;
$sth1 = $dbh->prepare('SELECT *
    FROM users
    WHERE username IN(:name1,:name2)');
$sth->execute(array(':name1' => "admin",'name2'=>'guest'));
$sth->execute([]);
while($row = $sth->fetch(PDO::FETCH_ASSOC)){
    var_dump($row);
}

while($row = $sth->fetch(PDO::FETCH_ASSOC)){
    var_dump($row);
}
exit;*/
require('../Edge/Core/Loader.php');
use Edge\Core\Edge;
$webApp = new Edge("Application/Config/config.php");
$router = $webApp->getConfig('routerClass');
$oRouter = new $router($webApp->getRoutes());
$oRouter->invoke();