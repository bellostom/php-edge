<?php
return array(
    'services' => array(

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

        'cache' => array(
            'invokable' => 'Edge\Core\Cache\RedisCache',
            'args' => array(
                array('localhost:6379')
            ),
            'shared' => true
        ),

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
    'timezone' => 'Europe/Athens',
    'env' => 'development'
);