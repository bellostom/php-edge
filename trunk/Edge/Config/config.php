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

        'response' => array(
            'invokable' => 'Edge\Core\Http\Response',
            'args' => array(),
            'shared' => true
        ),

        'logger' => array(
            'invokable' => function($c){
                $dateFormat = "Y n j, g:i a";
                $output = "%datetime% > %level_name% > %message% %context%\n";
                $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);
                $stream = new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::DEBUG);
                $stream->setFormatter($formatter);
                $log = new Monolog\EdgeLogger('Edge');
                $log->pushHandler($stream);
                return $log;
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
    'notFound' => array("Home", "notFound"),
    'serverError' => array("Home", "serverError"),
    'routerClass' => 'Edge\Core\Router',
    'timezone' => 'Europe/Athens',
    'env' => 'production'
);