<?php
namespace Edge\Models;
use Edge\Core\Database;

abstract class Identifiable extends ActiveRecord{
    protected static $_members = array(
        'id', 'name'
    );

	public static function getItemByName($value, $fetchMode=Identifiable::FETCH_INSTANCE) {
        return parent::find(array("name" => $value), array("fetchMode" => $fetchMode));
	}

	public static function getItemById($id)	{
        return parent::find($id);
	}
}
?>