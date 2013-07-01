<?php
namespace Edge\Models;

class UserRole extends Record{

    public static function getTable(){
        return 'user_role';
    }

    protected static $_members = array('user_id', 'role_id');

    public static function getPk(){
        return array('user_id', 'role_id');
    }

}