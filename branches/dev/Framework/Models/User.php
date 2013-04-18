<?php
namespace Framework\Models;
use Framework\Core\Database\DB;

class User extends Identifiable {
	public $pass;
	public $username;
	public $surname;
	public $salt;
	public $role;
	public static $table = array(
						  "table"=>"users",
						  "PK"=>array(
			  			   	"id"=>null
			  			  )
						 );
	const GUEST = 1;
	const ADMIN = 3;
	const AUTH_USER = 5;

	const ROLE_NONE = '0';
	const ROLE_EDITOR = '1';
	const ROLE_PUBLISHER = '2';
	const ROLE_ADMIN = '4';

	public function __construct(array &$data=array()) {
		parent::__construct($data);
		if($this->salt == '')
			$this->salt = Utils::genRandom();
	}

	/**
	 * Abstract method implementation
	 * Return an instance of the stored user
	 * @see intralot/schema/Publishable::getItem()
	 */
	public function getItem()
	{
		return User::getItemById($this->id);
	}

	/**
	 * Abstract method implementation
	 * Get the objet description, for logging
	 * purposes.
	 * @see intralot/schema/Publishable::getActionDescription()
	 */
	protected function getActionDescription()
	{
		return sprintf("User %s %s", $this->name,
									 $this->surname);
	}

	/**
	 *
	 * Return a textual representation
	 * of the user's cms role
	 */
	public function getCmsRole()
	{
		$roles = array(
			1 => 'editor',
			2 => 'publisher',
			4 => 'admin'
		);
		return $roles[$this->role];
	}

	/**
	 *
	 * Check if the user has access
	 * to the cms area
	 */
	public function hasCmsAccess()
	{
		return $this->role != User::ROLE_NONE;
	}

	/**
	 *
	 * Check if user has publishing
	 * privileges
	 */
	public function isPublisher()
	{
		return $this->role == User::ROLE_PUBLISHER ||
				$this->role == User::ROLE_ADMIN;
	}

	public function isAdmin()
	{
		return $this->role == User::ROLE_ADMIN;
	}

	/**
	 *
	 * Get all users
	 */
	public static function getUsers()
	{
		return parent::getAllItemsByTable();
	}

	/**
	 *
	 * Get only cms users
	 */
	public static function getCmsUsers()
	{
		$q = "SELECT * FROM users WHERE id > 5";
		return parent::query($q, true);
	}

	/**
	 *
	 * Get user by id
	 * @param int $id
	 */
	public static function getUserById($id)
	{
		return parent::getItemById($id);
	}

	/**
	 *
	 * Get user by username
	 * @param string $name
	 */
	public static function getUserByUsername($name)
	{
		$db = DB::getInstance();
		$q = sprintf("SELECT * FROM %s
					  WHERE username='%s'",  static::getTable(),
					  $db->db_escape_string($name));
		return parent::query($q);
	}

	/**
	 *
	 * Authenticate user
	 * @param string $pass
	 */
	public function authenticate($pass){
		$pass = sha1($this->salt.$pass);
		return $this->pass == $pass;
	}

	/**
	 *
	 * SHA1 encode passwd before saving.
	 * Prepend a random string (unique per user)
	 * to avoid rainbow attacks.
	 */
	private function encodePassword()
	{
		$this->pass = sha1($this->salt.$this->pass);
	}

	/**
	 * Interface implementation
	 * Encode pass before saving
	 * @see intralot/schema/Publishable::on_create()
	 */
	public function on_create()
	{
		$this->encodePassword();
		parent::on_create();
	}

	/**
	 * Interface implementation
	 * Before updating user check if passwd
	 * has changed
	 * @see intralot/schema/Publishable::on_update()
	 */
	public function on_update()
	{
		$user = $this->getItem();
		if(empty($this->pass))
			$this->pass = $user->pass;
		else if($user->pass != $this->pass)
			$this->encodePassword();
		parent::on_update();
	}
}
?>