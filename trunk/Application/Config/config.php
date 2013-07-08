<?php
/**
 * You can define or override services and settings
 * Check Edge/Config/config.php for a list of services and
 * configurations options.
 * To access a service
 * Edge::app()->serviceName;
 * To access a configuration option
 * Edge::app()->getConfig('timezone');
 */
return array(
    'services' => array(
        /**
         * Redis caching storage
         */
        'cache' => array(
            'invokable' => 'Edge\Core\Cache\RedisCache',
            'args' => array('localhost:6379'),
            'shared' => true
        )
    ),
    'routes' => include(__DIR__."/routes.php"),
    'notFound' => array("\Application\Controllers\Home", "notFound"),
    'serverError' => array("\Application\Controllers\Home", "serverError"),
    'timezone' => 'Europe/Athens',
    'env' => 'development'
);