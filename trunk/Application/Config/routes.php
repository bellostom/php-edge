<?php
return array(
    //controller/action => URI matching
    'POST' => array(
        /*'home/index' => '/',
        'foo' => 'page/:action/:name/:id',
        'bar/thomas' => 'page/:action'*/
        '/' => array("Home", "index"),
        '/page/action/:name/:id' => array("Home", "index"),
        '/new/test/:id' => array("Home", "test"),
        '/θωμα' => array("Home", "index")

    ),
    'GET'=>array(),
    '*' => array()
);