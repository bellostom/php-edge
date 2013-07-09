<?php
namespace Edge\Models;

class RolePermission extends Record{

    public static function getTable(){
        return 'role_perm';
    }

    protected static $_members = array('role_id', 'perm_id');

    public static function getPk(){
        return array('role_id', 'perm_id');
    }

}