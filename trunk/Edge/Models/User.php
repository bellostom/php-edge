<?php
namespace Edge\Models;
use Edge\Utils\Utils;


class User extends Identifiable {
    protected static $_members = array(
        'username', 'pass', 'salt', 'surname'
    );

	const GUEST = 1;
	const AUTH_USER =3;

	public function __construct(array &$data=array()) {
		parent::__construct($data);
		if($this->salt == '')
			$this->salt = Utils::genRandom();
	}

    public static function getPk(){
        return array("id");
    }

    public static function getTable(){
        return 'users';
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
        return parent::find(array(
            "conditions" => array("username" => $name)
        ));
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
?>