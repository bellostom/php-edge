<?php
return array(
    //controller/action => URI matching
    'GET' => array(
        'home/index' => '/',
        'foo' => 'page/:action/:name/:id',
        'bar/thomas' => 'page/:action',
        'home/index' => 'hello/invalid'
    ),
    '*' => array()
);