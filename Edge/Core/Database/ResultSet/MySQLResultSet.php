<?php
namespace Edge\Core\Database\ResultSet;

use Edge\Core\Database\DB;

class MySQLResultSet extends ResultSet {

	protected function setRows(){
		$db = DB::getInstance();
        $this->totalRows = $db->db_num_rows($this->result);
	}

	protected function getRecord($offset){
		$db = DB::getInstance();
		$db->db_seek($this->result, $offset);
        $row = $db->db_fetch_array($this->result);
		return new $this->className($row);
	}
}
?>