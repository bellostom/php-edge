<?php
return array(
    'services' => array(

        'memoryCache' => array(
            'invokable' => 'Edge\Core\Cache\MemoryCache',
            'args' => array(
                array('master:11311:1')
            ),
            'shared' => true
        ),

        'request' => array(
            'invokable' => 'Edge\Core\Http\Request',
            'args' => array(),
            'shared' => true
        ),

        'response' => array(
            'invokable' => 'Edge\Core\Http\Response',
            'args' => array(),
            'shared' => true
        ),

        'logger' => array(
            'invokable' => 'Edge\Core\Logger\Logger',
            'args' => array('/var/log/phorm.log', 'phpfrm', '%a, %d %b %Y %X'),
            'shared' => true
        ),

        'mysqlCredentials' => array(
            'master' => array(
                'host' => '127.0.0.1:3306',
                'db' => 'frm',
                'user' => 'root',
                'pass' => ''
            ),
            'slave' => array(
                'host' => '127.0.0.1:3306',
                'db' => 'frm',
                'user' => 'root',
                'pass' => ''
            )
        ),

        'db' => array(
            'invokable' => function($c){
                static $obj;
                if(is_null($obj)){
                    $c['isTransactional'] = false;
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
        ),

        'user' => array(
            'invokable' => function($c){
                return new Edge\Models\User($c['db']);
            }
        ),

        'notFound' => array("Home", "notFound"),

        'serverError' => array("Home", "serverError"),

        'sessionStorage' => 'Edge\Core\Session\SessionMemcacheStorage',

        'session' => array(
            'invokable' => function($c){
                $settings = array(
                    'session.name' => 'edge',
                    'session.timeout' => 20,
                    'session.path' => '/tmp/session',
                    'link' => $c['memoryCache']
                );
                return new Edge\Core\Session\Session($c['sessionStorage'], $settings);
            },
            'shared' => true
        )
    ),
    'routes' => include(__DIR__."/routes.php"),
    'timezone' => 'Europe/Athens'
);