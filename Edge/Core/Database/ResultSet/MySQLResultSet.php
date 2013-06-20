<?php
namespace Edge\Core\Database\ResultSet;

use Edge\Core\Edge;

class MySQLResultSet extends ResultSet {

	protected function setRows(){
		$db = Edge::app()->db;
        $this->totalRows = $db->db_num_rows($this->result);
	}

	protected function getRecord($offset){
        $db = Edge::app()->db;
		$db->db_seek($this->result, $offset);
        $row = $db->db_fetch_array($this->result);
		return new $this->className($row);
	}
}
?>