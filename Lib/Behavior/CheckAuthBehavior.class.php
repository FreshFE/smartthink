<?php

// 检查登录状态的行为类
class CheckAuthBehavior extends Behavior {

	protected $options = array(

		'AUTH_ON' => false,				// 是否开启权限认证
		'AUTH_RULES' => array(),		// 权限认证的规则
		'AUTH_ROLES' => array(),		// 权限认证的角色
		'AUTH_USER_ROLE_MODEL' => '',	// 权限认证的查询模型
		'AUTH_PACKAGE_NAME' => 'Library/Auth/RbacAuth',
		'AUTH_PACKAGE_PATH' => CORE_PATH,
		'AUTH_CLASS' => 'Think\Library\RbacAuth'
	);

	public function run(&$return) {
		
		if(C('AUTH_ON')) {

			// 开始check
			$auth = $this->check();

			// 将对象放入配置全局
			C('AUTH', $auth);
		}
	}

	private function check() {

		// 依赖载入
		Import::uses(C('AUTH_PACKAGE_NAME'), C('AUTH_PACKAGE_PATH'));

		// 类实例化
		$class = C('AUTH_CLASS');
		$auth = new $class(C('AUTH_KEY'), C('AUTH_RULES'), C('AUTH_ROLES'));

		// 检查
		if(!$auth->check()) {

			if($auth->session()) {
				// Redirect::error('您已经登录', U('Index/index'));
				$auth->error('您已经登录', U('Index/index'));
			}
			else {
				// Redirect::error('您未登录，请先登录', U('Account/login'));
				$auth->error('您未登录，请先登录', U('Account/login'));
			}
		}

		return $auth;
	}
}