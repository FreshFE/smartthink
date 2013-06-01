<?php namespace Think\Auths;

use Think\Request;
use Think\Exception;
use Think\Debug;

class UserSession
{
	protected static $user;

	public static function get()
	{
		if(is_null(static::$user)) {
			static::set();
		}

		return static::$user;
	}

	public static function set()
	{
		if(Request::getStorage('user')) {
			return static::$user = Request::getStorage('user');
		}
		else {
			Debug::output(new Exception("不存在Request Storage内的user"));
		}
	}

	public static function getId()
	{
		$user = static::get();

		if(isset($user['id'])) {
			return $user['id'];
		}
		else {
			Debug::output(new Exception("不存在请求的user_id"));
		}
	}
}