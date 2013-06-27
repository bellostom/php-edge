<?php
return array(
    //controller/action => URI matching
    'GET' => array(
        '/js/:file' => array("Edge\\Controllers\\Asset", "js"),
        '/css/:file' => array("Edge\\Controllers\\Asset", "css")

    )
);