<?php
namespace Edge\Models;
use Edge\Utils\Utils;


class User extends Identifiable {

    protected static $_members = array(
        'username', 'pass', 'salt', 'surname'
    );

    protected $_roles = array();

	const GUEST = 1;
	const AUTH_USER =3;

	public function __construct(array &$data=array()) {
		parent::__construct($data);
		if($this->salt == ''){
			$this->salt = Utils::genRandom();
        }
        if($this->pass && strlen($this->pass) != 40){
            $this->setPass($this->pass);
        }
	}

    public static function getPk(){
        return array("id");
    }

    public static function getTable(){
        return 'users';
    }

    /**
     * Load the roles that are assigned to the user
     * @return ResultSet
     */
    protected function roles(){
        return $this->manyToMany('Edge\Models\Role', array(
            'linkTable' => 'user_role',
            'fk1' => 'user_id',
            'fk2' => 'role_id',
            'value' => $this->id
        ));
    }

    /**
     * Check whether the user can execute the selected
     * action
     * @param string $perm
     * @return bool
     */
    public function hasPrivilege($perm) {
        foreach ($this->roles as $role) {
            if ($role->hasPerm($perm)) {
                return true;
            }
        }
        return false;
    }

    public function addRole(Role $role){
        $userRole = new UserRole();
        $userRole->user_id = $this->id;
        $userRole->role_id = $role->id;
        $userRole->save();
    }

	public function isAdmin(){
		return $this->id == User::ADMIN;
	}

    public function isGuest(){
        return $this->id == User::GUEST;
    }

	/**
	 *
	 * Get user by id
	 * @param int $id
	 */
	public static function getUserById($id)	{
		return parent::getItemById($id);
	}

	/**
	 *
	 * Get user by username
	 * @param string $name
	 */
	public static function getUserByUsername($name)	{
        return parent::select()
                        ->where(array("username" => $name))
                        ->fetch();
	}

	/**
	 *
	 * Authenticate user
	 * @param string $pass
	 */
	public function authenticate($pass){
		return $this->pass == $this->encodePassword($pass);
	}

    /**
     * Setter for the pass attribute
     * Automatically invoked each time
     * $this->pass = "someval" is called
     * @param $val
     */
    protected function setPass($val){
        $this->assignAttribute('pass', $this->encodePassword($val));
    }

	/**
	 *
	 * SHA1 encode passwd before saving.
	 * Prepend a random string (unique per user)
	 * to avoid rainbow attacks.
	 */
	private function encodePassword($pass){
		return sha1($this->salt.$pass);
	}
}