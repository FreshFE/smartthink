<?php

class CheckAuthBehavior extends Behavior {

	protected $options = array(

		'AUTH_ON' => false,				// 是否开启权限认证
		'AUTH_RULES' => array(),		// 权限认证的规则
		'AUTH_ROLES' => array(),		// 权限认证的角色
		'AUTH_USER_ROLE_MODEL' => '',	// 权限认证的查询模型
		'AUTH_TYPE' => 'RbacAuth'		// 权限认证的加载类类型
	);

	public function run(&$return) {
		
		if(C('AUTH_ON')) {

			if(C('AUTH_TYPE') == 'acl') {
				$auth = $this->acl();
			}

			else if(C('AUTH_TYPE') == 'rbac') {
				$auth = $this->rbac();
			}
			
			// 将对象放入配置全局
			C('AUTH', $auth);
		}
	}

	private function rbac() {

		import('COM.RbacAuth');
		$auth = new RbacAuth(C('AUTH_KEY'), C('AUTH_RULES'), C('AUTH_ROLES'));

		if(!$auth->check()) {

			if($auth->session()) {
				redirect(U('Index/index'));
			}
			else {
				// $jump = strtolower(GROUP_NAME .'-'. MODULE_NAME .'-'. ACTION_NAME);
				// redirect(U('Account/login', array('jump' => $jump)));
				redirect(U('Account/login'));
			}
		}

		return $auth;
	}

	private function acl() {

	}
}