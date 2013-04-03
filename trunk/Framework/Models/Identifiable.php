<?php
namespace Framework\Models;
use Framework\Core\Database;

class Identifiable extends Table{
	public $id;
	public $name;

	public static function getItemByName($value, $is_multi=true) {
		$db = Database\DB::getInstance();
		$q = sprintf("SELECT * FROM %s
					  WHERE name LIKE '%s%%'
					  ORDER BY name", static::getTable(),
					  $db->db_escape_string($value));
        return parent::query($q, true);
	}

	public function on_after_create() {
		if(DataMapping::columnHasFlag(static::getTable(), 'id', MYSQLI_AUTO_INCREMENT_FLAG)){
			$db = Database\WriteDB::getInstance();
			$this->id = $db->db_insert_id();
		}
		parent::on_after_create();
	}

	public static function getItemById($id)	{
		$db = Database\DB::getInstance();
		$query = sprintf("SELECT * FROM %s WHERE id='%s'",
						 static::getTable(),
						 $db->db_escape_string($id));
        return parent::query($query);
	}

	public static function getAllItemsByTable($orderby='name') {
		$db = Database\DB::getInstance();
		$q = sprintf("SELECT * FROM %s ORDER BY %s",
					 static::getTable(), $db->db_escape_string($orderby));
        return parent::query($q, true);
	}

	protected function elementExists($column, $value, $table, $exclude)	{
		$db = Database\DB::getInstance();
		$rs = $db->db_query("SELECT $column
							 FROM $table
							 WHERE $column='$value'
							 $exclude");
		return $db->db_num_rows($rs) > 0;
	}
}
?>