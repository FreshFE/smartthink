<?php
/**
 * Behavior/CheckAuthBehavior.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Behavior/CheckAuthBehavior
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * CheckAuthBehavior Class
 * 根据载入不同的类包来实例化Auth类并执行访问权检查
 * 执行后将Auth类返回至Config内
 */
class CheckAuthBehavior extends Behavior {

	/**
	 * 基础配置
	 * 在Behavior构造函数中，设置内容会和Config进行合并
	 * 如果Config中存在该值，则不合并，不存在则将下列默认值加入Config内
	 *
	 * var array
	 */
	protected $options = array(

		/**
		 * 是否开启权限认证
		 * true为开启，检查访问权
		 * false为关闭，不检查访问权
		 *
		 * var bealoon
		 */
		'AUTH_ON' => false,

		/**
		 * 权限认证的规则
		 * 必须在Config中定义
		 *
		 * var array
		 */
		'AUTH_RULES' => array(),

		/**
		 * 权限认证的角色
		 * 必须在Config中定义
		 *
		 * var array
		 */
		'AUTH_ROLES' => array(),

		/**
		 * 权限认证的查询模型
		 * 未使用功能
		 *
		 * var string
		 */
		'AUTH_USER_ROLE_MODEL' => '',

		/**
		 * 类包的加载名称
		 *
		 * var string
		 */
		'AUTH_PACKAGE_NAME' => 'Library/Auth/RbacAuth',

		/**
		 * 类包的加载路径
		 *
		 * var string
		 */
		'AUTH_PACKAGE_PATH' => CORE_PATH,

		/**
		 * Auth类包的名称
		 *
		 * var string
		 */
		'AUTH_CLASS' => 'Think\Library\RbacAuth',

		/**
		 * 已经登录的跳转地址
		 *
		 * var string
		 */
		'AUTH_LOGINED_URL' => 'Index/index',

		/**
		 * 未登录的跳转地址
		 *
		 * var string
		 */
		'AUTH_UNLOGIN_URL' => 'Account/login'
	);

	/**
	 * 实现Behavior的抽象方法
	 * 运行CheckAuthBehavior类
	 *
	 * @param mix &$return
	 *
	 * @return void
	 */
	public function run(&$return) {
		
		if(C('AUTH_ON')) {

			// 开始check
			$auth = $this->check();

			// 将对象放入配置全局
			C('AUTH', $auth);
		}
	}

	/**
	 * 检查Auth功能
	 * 可根据配置载入不同的类包和执行不同的类
	 * 被执行类需要实现check, session和error三个方法
	 *
	 * @return object 实例化的auth对象
	 */
	private function check() {

		// 依赖载入
		Import::load(CORE_PATH . 'Library/Auth/RbacAuth' . EXT);

		// 类实例化
		$class = C('AUTH_CLASS');
		$auth = new $class(C('AUTH_KEY'), C('AUTH_RULES'), C('AUTH_ROLES'));

		// 检查访问权
		if(!$auth->check()) {

			// 已经登录
			if($auth->session()) {
				$auth->error('您已经登录', U(C('AUTH_LOGINED_URL')));
			}

			// 未登录
			else {
				$auth->error('您未登录，请先登录', U(C('AUTH_UNLOGIN_URL')));
			}
		}

		return $auth;
	}
}