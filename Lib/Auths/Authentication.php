<?php namespace Think\Auths;

use Think\Session;
use Think\Cookie;
use Think\Model;
use Think\Request;
use Think\Log;

/**
 * 用户认证相关类
 * 主要方法
 * $this->check 检查认证信息
 * $this->save 保存认证信息
 * $this->remove 移除认证信息
 * $this->getUser 得到用户信息
 */
class Authentication
{
	/**
	 * Session 名称
	 *
	 * @var string
	 */
	public $sessionName = 'USER_SESSION';

	/**
	 * Cookie 名称
	 *
	 * @var string
	 */
	public $cookieName = 'SMARTUSERAUTH';

	/**
	 * 是否为匿名用户
	 *
	 * @var bealoon
	 */
	protected $isAnonymous = true;

	/**
	 * 从服务器得到的或将写入服务器 Session 存储
	 *
	 * @var array
	 */
	protected $session;

	/**
	 * 用户模型的提供商
	 *
	 * @var object
	 */
	protected $userModelProvider;

	/**
	 * 用户信息
	 * 当$this->isAnonymous为true的时候，$this->user为null
	 *
	 * @var array
	 */
	protected $user;

	/**
	 * 构造函数
	 */
	public function __construct($modelName = null)
	{
		if(!is_null($modelName)) {
			$this->setUserModelProvider($modelName);
		}

		Log::info('request_cookie', array('request' => $_COOKIE));
	}

	/**
	 * 设置UserModel提供对象
	 *
	 * @param string $modelName
	 * @return $this
	 */
	public function setUserModelProvider($modelName)
	{
		$this->userProvider = new Model($modelName);

		return $this;
	}

	/**
	 * 检查是否存在session
	 * 是否开启检查cookie，即isRemenberme
	 * session不存在则检查cookie
	 * 都不存在设为anonymous匿名用户
	 * 存在则获取用户信息存在$this->user
	 */
	public function check()
	{
		// 检查 Session 和 Cookie
		if($this->checkSession()) {
			$this->setIsAnonymous(false);
		}
		else if($this->checkCookie())
		{
			$this->setIsAnonymous(false);
		}

		// 非匿名用户获得设置 User 信息
		if(!$this->isAnonymous) {

			// 用户信息
			$user = $this->setUser($this->getSession());

			// 认证通过，写入User信息
			Request::setStorage('user', $user);
		}

		return $this;
	}

	/**
	 * 设置用户信息
	 * 主要是将Session内的信息设置到$this->user内
	 *
	 * @param array $user
	 * @return array
	 */
	public function setUser($user)
	{
		return $this->user = $user;
	}

	/**
	 * 得到用户信息
	 *
	 * @return array
	 */
	public function getUser()
	{
		return $this->user;
	}

	public function getUserRole()
	{
		if($this->isAnonymous) {
			return 'ROLE_ANONYMOUS';
		}
		else {
			$user = $this->user;
			return isset($user['role']) ? $user['role'] : 'ROLE_ANONYMOUS';
		}
	}

	/**
	 * 检查 Session
	 *
	 * @return boolean true => 存在session，false => 不存在session
	 */
	protected function checkSession()
	{
		// 获得
		$session = Session::get($this->sessionName);

		// 检查和设置
		if($session) {
			$this->setSession($session);
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * 设置 Session
	 *
	 * @param array $session
	 * @return array
	 */
	public function setSession($session)
	{
		return $this->session = $session;
	}

	/**
	 * 得到 Session
	 *
	 * @return array
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * 检查 Cookie
	 *
	 * @return boolean true => 通过Cookie，false => 不存在Cookie
	 */
	protected function checkCookie()
	{
		// 获取
		$cookie = Cookie::get($this->cookieName);

		$cookie = explode('|', $cookie);

		$cookie = array(
			'email' => $cookie[0],
			'password' => $cookie[1]
		);

		// 不存在cookie
		if(!$cookie) return false;

		// 不存在记录
		if(!M('User')->where(array('email' => $cookie['email']))->find()) return false;

		// 哈希不正确
		if($cookie['password'] !== md5($data['email'] . $data['password'] . $data['password_salt'])) return false;

		// 记录Session
		$this->saveSession();

		return true;
	}

	/**
	 * 得到 isAnonymous
	 *
	 * @return boolean
	 */
	public function getIsAnonymous()
	{
		return $this->isAnonymous;
	}

	/**
	 * 设置 isAnonymous
	 *
	 * @param boolean $isAnonymous
	 * @return boolean
	 */
	protected function setIsAnonymous($isAnonymous)
	{
		return $this->isAnonymous = $isAnonymous;
	}

	/**
	 * 存储用户信息到 Session 和 Cookie
	 *
	 * @param $user
	 * @param boolean $isRemember 是否记住密码
	 * @return $this
	 */
	public function save(array $user, $isRemember = false)
	{
		// 用户基本信息
		$session = array(
			'id' => $user['id'],
			'email' => $user['email'],
			'username' => $user['username'],
			'password_cookie' => md5($user['email'] . $user['password'] . $user['password_salt']),
			'createline' => $user['createline'],
			'role' => $user['role']
		);

		// 存储 Session
		$this->saveSession($session);

		// 是否记住
		if($isRemember) {
			$cookie = join(array($session['email'], $session['password_cookie']), '|');
			$this->saveCookie($cookie);
		}

		return $session;
	}

	/**
	 * 保存 Session
	 *
	 * @param $session
	 * @return $this
	 */
	protected function saveSession($session)
	{
		Session::set($this->sessionName, $session);

		return $this;
	}

	/**
	 * 保存 Cookie
	 *
	 * @param $cookie
	 * @return $this
	 */
	protected function saveCookie($cookie)
	{
		Cookie::set($this->cookieName, $cookie);

		return $this;
	}

	/**
	 * 移除用户信息
	 *
	 * @return $this
	 */
	public function remove()
	{
		$this->removeSession()->removeCookie();

		return $this;
	}

	/**
	 * 清除 Session
	 *
	 * @return $this
	 */
	protected function removeSession()
	{
		Session::set($this->sessionName, null);

		return $this;
	}

	/**
	 * 清除 Cookie
	 *
	 * @return $this
	 */
	protected function removeCookie()
	{
		Cookie::set($this->cookieName, null);

		return $this;
	}
}