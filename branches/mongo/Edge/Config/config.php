<?php
return array(
    'services' => array(

        'cache' => array(
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

        'cookie' => array(
            'invokable' => 'Edge\Core\Http\Cookie',
            'args' => array(
                array(
                    'secure' => false,
                    'encrypt' => true,
                    'secret' => 'C7s9r7yYYyVCDZZstzyl',
                    'httpOnly' => true
                )
            ),
            'shared' => true
        ),

        'response' => array(
            'invokable' => 'Edge\Core\Http\Response',
            'args' => array(),
            'shared' => true
        ),

        'logger' => array(
            'invokable' => function($c){
                $attrs = array(
                    "file" => "app.log",
                    "dateFormat" => "j/n/Y g:i a",
                    "logLevel" => 'DEBUG'
                );
                return Monolog\EdgeLogger::factory($attrs);
            },
            'shared' => true
        ),

        'sessionStorage' => 'Edge\Core\Session\SessionMemcacheStorage',

        'session' => array(
            'invokable' => function($c){
                $settings = array(
                    'session.name' => 'edge',
                    'session.timeout' => 20*60,
                    'session.httponly' => true,
                    'session.path' => '/tmp/session',
                    'link' => $c['cache']
                );
                return new Edge\Core\Session\Session($c['sessionStorage'], $settings);
            },
            'shared' => true
        )
    ),
    'loginUrl' => '/home/login',
    'notFound' => array("\Application\Controllers\Home", "notFound"),
    'serverError' => array("\Application\Controllers\Home", "serverError"),
    'routerClass' => 'Edge\Core\Router',
    'userClass' => 'Edge\Models\User',
    'timezone' => 'Europe/Athens',
    'env' => 'production'
);