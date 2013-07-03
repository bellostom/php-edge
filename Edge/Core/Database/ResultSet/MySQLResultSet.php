<?php
namespace Edge\Core\Database\ResultSet;

use Edge\Core\Edge;

class MySQLResultSet extends ResultSet{

	protected function setRows(){
		$db = Edge::app()->db;
        $this->totalRows = $db->dbNumRows($this->result);
	}

	protected function getRecord($offset){
        $db = Edge::app()->db;
		$db->dbSeek($this->result, $offset);
        $row = $db->dbFetchArray($this->result);
		return new $this->className($row);
	}
}