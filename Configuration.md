**Edge** is confgured by editing the file

```
Application/Config/config.php
```

In this file you can define services and configurations options for your application.

By default, any options specified in this file that also exist in the main configuration file of the framework located at

```
Edge/Config/config.php
```

will override the default ones.

## Services and Config Options ##

**Edge** makes use of a Dependency Injection Container to register and expose services and configurations options.

Specifically, it makes use of [Pimple](http://pimple.sensiolabs.org/), in order to accommodate for these requirements.

Below is a sample configuration file that registers services and configuration options

```
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
                "servers" => array('master:11311:1'),
                "namespace" => "edge"
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
                'secure' => false,
                'sign' => true,
                'secret' => 'C7s9r7yYYyVCDZZstzyl',
                'httpOnly' => true
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
                    "file" => "app.log",
                    "dateFormat" => "j/n/Y g:i a",
                    "logLevel" => 'DEBUG'
                );
                return Edge\Core\Logger\Monolog\EdgeLogger::factory($attrs);
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
```

Let's have a look at what is going on here.

The services section is where we define the services we need to use in our web application. You can store attributes that will be used by a service, ie

```
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
```

or a service directly.

Additionally, by defining the

```
shared => true
```

in a service, this makes the service a singleton.

It is important to note here, that the services are initialized only when you request it (lazy loading) and not when the configuration file is processed, so there is no overhead regardless the number of services defined.

## Accessing a Service ##

To access a service from your application, do the below
```
use Edge\Core\Edge;

public function login($username, $pass){
   $user = User::getUserByUsername($username);
   if($user->authenticate($pass){
      Edge::app()->session->loggedin = true;
   }
}
```

## Accessing a Config Option ##

To access a service from your application, do the below
```
Edge\Core\Edge::app()->getConfig('routerClass');
```