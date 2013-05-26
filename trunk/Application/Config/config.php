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

        'logger' => array(
            'invokable' => 'Edge\Core\Logger\Logger',
            'args' => array('/var/log/phorm.log', 'phpfrm', '%a, %d %b %Y %X'),
            'shared' => true
        ),

        'mysqlCredentials' => array(
            'invokable' => array(
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
            'type' => 'variable'
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

        'sessionStorage' => array(
            'invokable' => 'Edge\Core\Session\SessionMemcacheStorage',
            'type' => 'variable'
        ),

        'session' => array(
            'invokable' => function($c){
                $settings = array(
                    'session.name' => 'bellos',
                    'session.timeout' => 20,
                    'session.path' => '/tmp/session',
                    'link' => $c['memoryCache']
                );
                return new Edge\Core\Session\Session($c['sessionStorage'], $settings);
            },
            'shared' => true
        )
    ),
    'timezone' => 'Europe/Athens'
);
/*$settings = new stdClass();

$settings->db_username = 'root';
$settings->db_passwd = '';
$settings->db_database = 'inooz';
$settings->use_cache = false;
$settings->default_lang = 'uk';

$settings->logger = new stdClass();
$settings->logger->file = '/var/log/application.log';
$settings->logger->dateFormat = '%a, %d %b %Y %X';
$settings->logger->identity = 'Application';

$settings->i18n_dir = $_SERVER['DOCUMENT_ROOT']."/portal/langs";
$settings->not_found = array('Home', 'notFound');
$settings->server_error = array('Home', 'serverError');
$settings->default_url = '/home';
$settings->default_method = 'index';

$settings->slave = '127.0.0.1:3306';
$settings->master = '127.0.0.1:3306';

$settings->memcached_servers = array(
    'master:11311:1'
);
*/