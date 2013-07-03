<?php
return array(
    'services' => array(
        /**
         * Define a variable to store MySQL credentials
         * for the master and slave nodes
         */
        'mysqlCredentials' => array(
            'master' => array(
                'host' => 'localhost:3306',
                'db' => 'edge',
                'user' => 'root',
                'pass' => ''
            ),
            'slave' => array(
                'host' => 'localhost:3306',
                'db' => 'edge',
                'user' => 'root',
                'pass' => ''
            )
        ),

        /**
         * Redis caching storage
         */
        'cache' => array(
            'invokable' => 'Edge\Core\Cache\RedisCache',
            'args' => array(
                array('localhost:6379')
            ),
            'shared' => true
        ),

        /**
         * Mongo connection object
         */
        'mongo' => array(
            'invokable' => 'Edge\Core\Database\MongoConnection',
            'args' => array(
                array(
                    'host' => 'localhost',
                    'db' => 'people'
                )
            ),
            'shared' => true
        ),

        'isTransactional' => false,

        'db' => array(
            'invokable' => function($c){
                static $obj;
                if(is_null($obj)){
                    $obj = new Edge\Core\Database\MysqlSlave($c['mysqlCredentials']['slave']);
                }
                return ($c['isTransactional'])?$c['writedb']:$obj;
            }
        ),

        'writedb' => array(
            'invokable' => function($c){
                $c['isTransactional'] = true;
                return new Edge\Core\Database\MysqlMaster($c['mysqlCredentials']['master']);
            },
            'shared' => true
        )
    ),
    'routes' => include(__DIR__."/routes.php"),
    'notFound' => array("\Application\Controllers\Home", "notFound"),
    'serverError' => array("\Application\Controllers\Home", "serverError"),
    'timezone' => 'Europe/Athens',
    'env' => 'development'
);