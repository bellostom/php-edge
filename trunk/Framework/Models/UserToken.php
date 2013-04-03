<?php
namespace Framework\Models;
use Framework\Core\Database\DB;

class UserToken extends Table
{
	public $uid;
	public $sid;
	public $token;
	public static $use_cache = false;
	public static $table = array(
						  "table"=>"user_token",
						  "PK"=>array(
			  			   	"uid"=>null,
							"sid"=>null,
							"token"=>null
			  			  )
						 );

	public function __construct(array &$data=array())
	{
		parent::__construct($data);
		if($this->token == '')
			$this->token = Utils::genRandom(30);
	}

	/*
	 * Send a http cookie to allow automatic login, in a
	 * safe manner.
	 */
	public static function setCookieToken(User $user, $salt)
	{
		$instance = new UserToken();
		$instance->sid = $salt;
		$instance->uid = $user->id;
		$instance->save(true);
		$cookie_val = sprintf('%s_%s', $instance->sid, $instance->token);
		$context = Context::getInstance();
		$context->session->set_cookie('frmauth', $cookie_val, time()+60*60*24*30);
	}

	public static function getBySidAndToken($sid, $token)
	{
		$q = "SELECT * FROM user_token
			  WHERE sid='$sid'
			  AND token='$token'";
		return forward_static_call(array('Table', 'query'), $q);
	}

	public static function deleteByUid($uid)
	{
		$all = UserToken::getByUid($uid);
		if($all instanceof UserToken)
			$all->delete();
		if($all instanceof ObjectSet){
			foreach($all as $a)
				$a->delete();
		}
	}

	public static function getBySid($sid)
	{
		$q = "SELECT * FROM user_token
			  WHERE sid='$sid'";
		return forward_static_call(array('Table', 'query'), $q);
	}

	public static function getByUid($uid)
	{
		$q = "SELECT * FROM user_token
			  WHERE uid='$uid'";
		return forward_static_call(array('Table', 'query'), $q);
	}
}
?>