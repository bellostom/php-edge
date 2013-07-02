<?php
return array(
    'GET' => array(
        '/' => array("Home", "index"),
        '/page/action/:name/:id' => array("Home", "index"),
        '/new/test/:id' => array("Home", "test"),
        '/view/city/:id' => array("Home", "city"),
        '/home/session' => array("Limited", "index")

    ),
    'POST' => array(
        '/rest/api/:id' => array('Home', 'post')
    ),
    '*' => array(
        '/api/update/:id' => array("Home", "test")
    )
);