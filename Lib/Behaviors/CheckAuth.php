<?php namespace Think\Behaviors;

use Think\Behavior;
use Think\Config;
use Think\Session;
use Think\Cookie;
use Think\Response;
use Think\Redirect;
use Think\Url;
use Think\Lang;
use Think\Exception;
use Think\Auths\Authentication;
use Think\Auths\Authorization;

/**
 * 检查用户认证和用户授权的行为类
 * 一般在路由和开启Session后，但在分配到各个控制器前的APP_AUTH行为钩子里加入
 * 本类先进行认证，确定用户身份后，进行授权
 * 当AJAX执行时，需要通过认证的情况返回401，未获得授权无权操作该功能的情况下返回403
 * 游览器执行时，需要通过认证的情况跳转至登录页面，未获得授权无权操作该功能的情况下转移到403提示页面
 */
class CheckAuth extends Behavior
{
	/**
	 * 用户的角色的规则
	 * 支持在配置中定义
	 *
	 * @var array
	 */
	public $roleRules;

	/**
	 * 用户模型提供者名称
	 *
	 * @var string
	 */
	public $modelName = 'User';

	/**
	 * 非ajax情况下的401跳转的页面
	 *
	 * @var string
	 */
	public $loginPage = 'account/login';

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

	/**
	 * 返回401
	 * 可以根据实际情况在项目内覆盖
	 *
	 * @return void
	 */
	public function redirectTo401()
	{
		if(IS_AJAX) {
			exit(Response::send_http_status(401));
		}
		else {
			Redirect::error(Lang::get('_ERROR_401'), Url::make($this->loginPage));
		}
	}

	/**
	 * 返回403
	 * 可以根据实际情况在项目内覆盖
	 *
	 * @return void
	 */
	public function redirectTo403()
	{
		if(IS_AJAX) {
			exit(Response::send_http_status(403));
		}
		else {
			exit(Lang::get('_ERROR_403'));
		}
	}

	/**
	 * 检查认证
	 *
	 * @param $modelName 用户模型提供者
	 * @return obeject 执行认证检查后的认证对象，包含用户的检测信息
	 */
	protected function checkAuthentication($modelName)
	{
		$driver = new Authentication($modelName);

		return $driver->check();
	}

	/**
	 * 检查授权
	 *
	 * @param $userRole 用户当前的角色，通过由Authentication类检查后提供
	 * @param $roleRules 用户角色的规则
	 * @return boolean 是否通过授权
	 */
	protected function checkAuthorization($userRole, $roleRules)
	{
		// 实例化
		$driver = new Authorization($userRole, $roleRules);

		// 返回检查结果
		return $driver->check();
	}
}