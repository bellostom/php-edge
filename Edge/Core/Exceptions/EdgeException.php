<?php
namespace Edge\Core\Exceptions;

use Edge\Core\Edge,
    Edge\Core\Database\MysqlMaster;

class EdgeException extends \Exception{

	public function __construct($message, $logError=true, $logBackTrace=true) {
        parent::__construct($message);
        //if there is an active transaction, roll it back
        if(Edge::app()->db instanceof MysqlMaster){
            Edge::app()->db->rollback();
        }
        if($logBackTrace) {
            Edge::app()->logger->err($message. "\\n". $this->getTraceAsString());
        }
        elseif($logError){
            Edge::app()->logger->err($this->message);
        }
    }
}