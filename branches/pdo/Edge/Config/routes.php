<?php
return array(
    'GET' => array(
        '/js/:file' => array("Edge\\Controllers\\Asset", "js"),
        '/css/:file' => array("Edge\\Controllers\\Asset", "css")
    )
);