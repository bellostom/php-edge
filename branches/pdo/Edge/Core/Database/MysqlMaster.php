<?php
namespace Edge\Core\Database;

use Edge\Core\Edge;

class MysqlMaster extends MysqlSlave {

    public function startTransaction()	{
        if(!$this->link->inTransaction()) {
            $this->link->beginTransaction();
            Edge::app()->logger->info("START TRANSACTION");
        }
    }

    public function commit() {
        if($this->link->inTransaction()) {
            $this->link->commit();
            Edge::app()->logger->info("COMMIT");
        }
    }

    public function rollback() {
        $this->link->rollback();
        Edge::app()->logger->info("ROLLBACK");
    }

    public function __destruct() {
        if($this->link->inTransaction()){
            Edge::app()->logger->info("COMMITING NON COMMITED TRANSACTION");
            $this->commit();
        }
        parent::__destruct();
    }
}