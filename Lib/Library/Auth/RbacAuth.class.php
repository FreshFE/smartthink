<?php namespace Think\Library;
/**
 * Library/RbacAuth.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Library.RbacAuth
 * @since         Smart ThinkPHP Extend 1.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

\Import::uses('Auth', dirname(__FILE__));

/**
 * 配置方法
 */
/*
return array(

	'AUTH_KEY' => 'USER_AUTH_KEY',

	'AUTH_RULES' => array(

		// Public
		1 => array(
		    'Home/Index/index' => true,

		    'Home/Account/login' => true,
		    'Home/Account/register' => true,
		    'Home/Account/forget' => true,

		    'Home/Recommend/index' => true,
		    'Home/Recommend/clear' => true,
		    'Home/Recommend/location' => true
		),

		// Member
		2 => array(
		    'Home' => true,
		    
		    'Home/Account/login' => false,
		    'Home/Account/register' => false,
		    'Home/Account/forget' => false
		),

		// Superadmin
		3 => array(
			'Admin' => true
		)
	),

	// 规则
	'AUTH_ROLES' => array(

		// Public
		1 => array(
			'name' => 'Public',
			'adapter_id' => 1,
			'basetype' => true
		),

		// Member
    	2 => array(
    		'name' => 'Member',
    		'adapter_id' => 2,
    		'basetype' => true
		),

    	// SuperAdmin
		3 => array(
			'name' => 'SpuerAdmin',
			'adapter_id' => array(2,3),
			'basetype' => true
		)
	)
);
*/

/**
 * RbacAuth Class Extends Auth
 * 继承Auth的主要功能
 * 拓展了adapter的处理方式，根据用户角色来适配adapter
 *
 * 依赖外部函数
 * @Function session()
 * @Function M()
 */
class RbacAuth extends Auth {

	/**
	 * 存放用户组的规则
	 *
	 * 格式 array(1 => array('name' => '', 'adapter_id' => 1))
	 *
	 * var array
	 */
	public $roles;

	/**
	 * 构造函数
	 *
	 * @param string $auth_key 关键词
	 * @param array $rules adapter规则
	 * @param array $roles 用户角色规则
	 *
	 * @return void
	 */
	public function __construct(string $auth_key, array $rules, array $roles) {

		// 执行父类构造函数
		parent::__construct($auth_key, $rules);

		// 定义用户角色列表
		if($roles) {
			$this->roles = $roles;
		}
	}

	/**
	 * 设置适配器
	 * 覆盖父类内方法
	 *
	 * $this->adapter
	 * @return void
	 */
	protected function setAdapter() {

		// 查询session是否存在
		if($this->logined()) {

			$this->adapter = $this->getRoleAdapter();
		}

		// 不存在，未登录
		else {
			$this->adapter = $this->getRoleAdapter(1);
		}
	}

	/**
	 * 得到用户的适配器配置
	 *
	 * @param $role_id int 用户组id
	 *
	 * @return array 用户组适配器列表
	 */
	protected function getRoleAdapter(int $role_id) {

		// 是否设置了$role_id
		if(!$role_id) {

			$role_id = $this->getRoleId();
			$role_id = $role_id ? $role_id : 2;
		}

		// 获得适配器id
		$adapter_id = $this->roles[$role_id]['adapter_id'];

		// 如果是数组的话，则遍历合并再返回
		if(is_array($adapter_id)) {

			$rules = array();

			foreach ($adapter_id as $key => $value) {
				$rules = array_merge($rules, $this->rules[$value]);
			}

			return $rules;
		}

		// 返回适配器设置
		return $this->rules[$adapter_id];
	}

	/**
	 * 查询数据库检索用户的role_id
	 *
	 * @return $role_id 用户组id
	 */
	protected function getRoleId() {

		// 设置查询条件
		$condition['user_id'] = $user_id;

		// 获得role_id
		$role_id = M('UserRole')->where(array('user_id' => $this->session()))->getField('role_id');
			
		// 设置默认的角色
		return $role_id;
	}
}