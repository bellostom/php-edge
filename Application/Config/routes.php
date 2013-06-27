<?php
return array(
    //controller/action => URI matching
    'GET' => array(
        '/' => array("Home", "index"),
        '/page/action/:name/:id' => array("Home", "index"),
        '/new/test/:id' => array("Home", "test"),
        '/view/city/:id' => array("Home", "city"),
        '/home/session' => array("Home", "test")

    ),
    'POST' => array(
        '/rest/api/:id' => array('Home', 'post')
    ),
    '*' => array(
        '/api/update/:id' => array("Home", "test")
    )
);