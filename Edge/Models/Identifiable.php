<?php
namespace Edge\Models;

abstract class Identifiable extends Record{

    protected static $_members = array(
        'id', 'name'
    );

    public static function getPk(){
        return array("id");
    }

	public static function getItemByName($value, $fetchMode=Identifiable::FETCH_INSTANCE) {
        return parent::select()
                        ->where(array("name" => $value))
                        ->fetch($fetchMode);
	}

	public static function getItemById($id)	{
        return parent::select()
                        ->where(array("id" => $id))
                        ->fetch();
	}

    public static function getAll($fetchMode=Identifiable::FETCH_RESULTSET, $orderBy=['name'=>'asc']){
        return parent::select()->order($orderBy)->fetch($fetchMode);
    }
}