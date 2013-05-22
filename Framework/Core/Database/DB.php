<?php
namespace Framework\Core\Database;
use Framework\Core;

class DB extends MysqlSlave {

    public static function getInstance() {
        if(self::$isTransactional){
            return WriteDB::getInstance();
        }
        return parent::getInstance();
    }

    protected function connect() {
        $settings = Core\Settings::getInstance();
        parent::_connect($settings->slave, $settings->db_database,
                         $settings->db_username, $settings->db_passwd);
    }
}
?>