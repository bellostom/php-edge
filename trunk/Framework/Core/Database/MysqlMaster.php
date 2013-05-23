<?php
namespace Framework\Core\Database;

class MysqlMaster extends MysqlSlave {
    private $isTransactional = false;

    public function start_transaction()	{
        if(!$this->isTransactional) {
            $this->db_query("START TRANSACTION");
            $this->isTransactional = true;
        }
    }

    public function commit() {
        if($this->isTransactional) {
            $this->link->commit();
            $this->isTransactional = false;
        }
    }

    public function rollback() {
        $this->link->rollback();
        $this->isTransactional = false;
    }
}
?>