<?php
namespace Framework\Core\Database;

class WriteDB extends BaseDB {

    protected function connect() {
        $settings = Settings::getInstance();
        parent::_connect($settings->master, $settings->db_database,
            $settings->db_username, $settings->db_passwd);
    }

    public function start_transaction()	{
        if(!self::$isTransactional) {
            $this->db_query("START TRANSACTION");
            self::$isTransactional = true;
        }
    }

    public function commit() {
        if(self::$isTransactional) {
            $this->link->commit();
            self::$isTransactional = false;
        }
    }

    public function rollback() {
        $this->link->rollback();
        self::$isTransactional = false;
    }
}
?>