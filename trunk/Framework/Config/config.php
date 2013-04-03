<?php
$settings = new stdClass();

$settings->user_class = 'Framework\Models\User';
$settings->db_username = '';
$settings->db_passwd = '';
$settings->db_database = '';
$settings->use_cache = true;
$settings->default_lang = 'uk';

$settings->logger = new stdClass();
$settings->logger->file = '/var/log/phorm.log';
$settings->logger->dateFormat = '%a, %d %b %Y %X';
$settings->logger->identity = 'Phrom';

$settings->slave = '127.0.0.1:3306';
$settings->master = '127.0.0.1:3306';

$settings->memcached_servers = array(
    'master:11211:1'
);

$settings->cache_dir = '/tmp';
?>