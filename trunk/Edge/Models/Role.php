<?php
namespace Edge\Models;

/**
 * Class Role
 * Part of the RBAC (Role Based Access Control) implementation
 * The Role class defines a single role
 * @package Edge\Models
 */
class Role extends Identifiable{

    protected $perms = array();

    public function __construct(array $attrs=array()){
        parent::__construct($attrs);
        if($this->id){
            $this->initPermissions();
        }
    }

    public static function getTable(){
        return 'roles';
    }

    /**
     * Load the permissions that the role has
     * @return ResultSet
     */
    protected function permissions(){
        return $this->manyToMany('Edge\Models\Permission', array(
            'linkTable' => 'role_perm',
            'fk1' => 'role_id',
            'fk2' => 'perm_id',
            'value' => $this->id
        ));
    }

    protected function initPermissions() {
        foreach($this->permissions as $perm){
            $this->perms[$perm->name] = true;
        }
    }

    /**
     * Check whether the role is permitted to execute the
     * action
     * @param string $permission
     * @return bool
     */
    public function hasPerm($permission) {
        return isset($this->perms[$permission]);
    }

}