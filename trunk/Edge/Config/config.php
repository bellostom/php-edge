<?php
return array(
    'services' => array(
        /**
         * Memcached for caching
         * We can pass as many servers as we want
         * The order is host:port:weight
         */
        'cache' => array(
            'invokable' => 'Edge\Core\Cache\MemoryCache',
            'args' => array(
                array('master:11311:1')
            ),
            'shared' => true
        ),

        /**
         * Class handling the process of incoming Requests
         */
        'request' => array(
            'invokable' => 'Edge\Core\Http\Request',
            'args' => array(),
            'shared' => true
        ),

        /**
         * Class responsible for reading and writing cookies
         */
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

        /**
         * Class responsible for sending output to the browser
         */
        'response' => array(
            'invokable' => 'Edge\Core\Http\Response',
            'args' => array(),
            'shared' => true
        ),

        /**
         * Logging class
         */
        'logger' => array(
            'invokable' => function($c){
                $attrs = array(
                    "file" => "../app.log",
                    "dateFormat" => "j/n/Y G:i:s",
                    "logLevel" => 'DEBUG'
                );
                return Monolog\EdgeLogger::factory($attrs);
            },
            'shared' => true
        ),

        /**
         * Define where the sessions are going to be saved
         */
        'sessionStorage' => array(
            'invokable' => function($c){
                return new Edge\Core\Session\SessionMemcacheStorage($c['cache']);
            },
            'shared' => true
        ),

        /**
         * Session class
         * Configuration options and initialization
         */
        'session' => array(
            'invokable' => function($c){
                $settings = array(
                    'session.name' => 'edge',
                    'session.timeout' => 20*60,
                    'session.httponly' => true
                );
                return new Edge\Core\Session\Session($c['sessionStorage'], $settings);
            },
            'shared' => true
        )
    ),
    /**
     * The below are configuration options
     */
    'loginUrl' => '/home/login',
    'routerClass' => 'Edge\Core\Router',
    'userClass' => 'Edge\Models\User',
    'timezone' => 'Europe/Athens',
    'env' => 'production'
);