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

        'isTransactional' => false,

        'db' => array(
            'invokable' => function($c){
                static $obj;
                if(is_null($obj)){
                    //$c['isTransactional'] = false;
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