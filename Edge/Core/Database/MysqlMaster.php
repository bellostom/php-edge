<?php
namespace Edge\Core\Database;

use Edge\Core\Edge;

class MysqlMaster extends MysqlSlave {
    private $isTransactional = false;

    public function startTransaction()	{
        if(!$this->isTransactional) {
            $this->dbQuery("SET AUTOCOMMIT=0");
            $this->dbQuery("START TRANSACTION");
            Edge::app()->logger->info("START TRANSACTION");
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
        if($this->isTransactional){
        $this->link->rollback();
        Edge::app()->logger->info("ROLLBACK");
        $this->isTransactional = false;
    }
    }

    public function __destruct() {
        if($this->isTransactional){
            $this->commit();
        }
        parent::__destruct();
    }
}