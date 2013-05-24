<?php namespace Think\Behaviors;

use Think\Behavior;
use Think\Config;
use Think\Session;
use Think\Cookie;
use Think\Response;
use Think\Exception;
use Think\Auths\Authentication;
use Think\Auths\Authorization;

class CheckAuth extends Behavior
{
	public $roleRules;

	public $modelName = 'User';

	/**
	 * 行为入口方法
	 *
	 * @param mixed &$params
	 * @return void
	 */
	public function run(&$params)
	{
		// 设置用户角色的规则
		if(is_null($roleRules)) {
			$this->roleRules = Config::get('AUTH_RULES');
		}

		// 认证
		$authentication = $this->checkAuthentication($this->modelName);

		// 授权
		$authorization = $this->checkAuthorization($authentication->getUserRole(), $this->roleRules);

		// 授权未通过
		if(!$authorization) {
			if($authentication->getUserRole() == 'ROLE_ANONYMOUS') {
				$this->redirectTo401();
			}
			else {
				$this->redirectTo403();
			}
		}
	}

	public function redirectTo401()
	{
		Response::send_http_status(401);
		exit();
	}

	public function redirectTo403()
	{
		Response::send_http_status(403);
		exit();
	}

	protected function checkAuthentication($modelName)
	{
		$driver = new Authentication($modelName);

		return $driver->check();
	}

	/**
	 * 检查授权
	 */
	protected function checkAuthorization($userRole, $roleRules)
	{
		// 实例化
		$driver = new Authorization($userRole, $roleRules);

		// 返回检查结果
		return $driver->check();
	}
}