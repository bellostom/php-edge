<?php
return array(
    //controller/action => URI matching
    'GET' => array(
        '/' => array("Home", "index"),
        '/page/action/:name/:id' => array("Home", "index"),
        '/new/test/:id' => array("Home", "test")

    ),
    '*' => array(
        '/api/update/:id' => array("Home", "test")
    )
);