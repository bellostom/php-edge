<?php
return array(
    'services' => array(
        'memoryCache' => array(
            'class' => 'Framework\Core\Cache\MemoryCache',
            'args' => array(
                array('master:11211:1')
            ),
            'shared' => true
        ),
        'logger' => array(
            'class' => 'Framework\Core\Logger\Logger',
            'args' => array('/var/log/phorm.log', 'phpfrm', '%a, %d %b %Y %X'),
            'shared' => true
        ),
        'db' => array(
            'class' => 'Framework\Core\Database\MysqlSlave',
            'args' => array('127.0.0.1:3306', 'frm', 'root', ''),
            'shared' => true
        ),
        'user' => function($c){
            return new Framework\Models\User($c['db']);
        }
    )
);
$settings = new stdClass();

$settings->db_username = 'root';
$settings->db_passwd = '';
$settings->db_database = 'frm';
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
?>