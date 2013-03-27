<?php namespace Think\Library;

\Import::uses('RbacAuth', dirname(__FILE__));

class ApiAuth extends RbacAuth {

	protected $token;

	public function __construct(string $auth_key, array $rules, array $roles) {

		// 执行父类构造函数
		parent::__construct($auth_key, $rules, $roles);

		// 获得token
		$this->token = $_POST['access_token'];
	}

	public function logined() {

		if($this->token && S($this->token)) {
			return true;
		}
		else {
			return false;
		}
	}

	public function login(int $id, string $token, int $expire) {

		// 设置默认值
		if(!$expire) $expire = 60 * 60 * 60 * 24 * 30;

		// 设置session并保存
		S($token, $id);
	}

	public function logout() {

		S($this->token, null);
	}

	public function session() {

		return S($this->token);
	}
}