<?php namespace Think\Library;
/**
 * Library/Auth.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Library.Auth
 * @since         Smart ThinkPHP Extend 1.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use Think\Session as Session;

/**
 * Auth Class
 * 判断当前模块是否允许当前用户访问，仅分unlogined和logined组
 * 检查当前用户是否登录
 * 保存用户登录时的session信息
 * 检索用户的session信息
 * 清除用户的session信息
 *
 * 实例化Auth类，在构造函数执行时，优先传入$auth_key和$rules参数
 */
class Auth {

	/**
	 * 过滤器访问规则，由实例化时通过构造函数初始化
	 *
	 * 格式 array(array('Home/Account/login' => true), array('Home' => true));
	 *
	 * 'Home' => true 表示Home分组下都被允许
	 * 'Home/Account' => true 表示Home分组下的Account模块都被允许
	 * 'Home/Account/login' => false 表示Home分组下的Account的login操作名不允许被访问
	 *
	 * $rules[0]表示的是unlogined(public)组的策略
	 * $rules[1]表示的是logined(member)组的策略
	 *
	 * var array
	 */
	public $rules;

	/**
	 * Auth认证的关键词，当保存session值的时候需要用到的key，用于区分session的名称
	 *
	 * var string
	 */
	public $auth_key;

	/**
	 * 在当前模块下是否允许当前用户访问
	 * 执行后$this->check()生效
	 * 不可以直接获得和修改$auth_pass的值，仅通过$this->pass()方法获得
	 *
	 * var bealoon
	 */
	protected $auth_pass;

	/**
	 * 当前用户角色的适配器列表
	 * 通过$this->setAdapter()方法，根据$this->rules规则，算出adapter值
	 * 供$this->access()判断当前用户执行
	 *
	 * var array
	 */
	protected $adapter;

	/**
	 * 构造函数
	 * 优先在构造函数处传入$auth_key和$rules
	 *
	 * @param string $auth_key session名称
	 * @param array $rules 访问规则，参考$this->rules的规则
	 */
	public function __construct($auth_key, $rules) {

		if($auth_key) {
			$this->auth_key = $auth_key;
		}

		if($rules) {
			$this->rules = $rules;
		}
	}

	/**
	 * 检查当前用户是否有权限访问当前模块
	 *
	 * @return bealoon true表示允许用户访问, false表示不允许用户访问
	 */
	public function check() {

		return $this->auth_pass = $this->access();
	}

	/**
	 * 检查用户是否已经登录
	 *
	 * @return bealoon true已经登录，false未登录
	 */
	public function logined() {

		// 当前仅仅检查session，不检查cookie
		return Session::check($this->auth_key);
	}

	/**
	 * 设置用户登录信息
	 *
	 * @param $id int 登录时保存的user_id
	 * @param $expire int session保存时间
	 */
	public function login($id, $expire) {

		// 设置默认值
		if(!$expire) $expire = 60 * 60 * 60 * 24 * 30;

		// 设置session并保存
		Session::config(array('name' => $this->auth_key, 'expire' => $expire));
		Session::set($this->auth_key, $id);
	}

	/**
	 * 退出用户登录信息
	 * 清除用户的session信息
	 *
	 * @return void
	 */
	public function logout() {

		Session::set($this->auth_key, null);
	}

	/**
	 * 获得用户保存的session值
	 *
	 * @return null|int，返回null表示用户未登录，返回int表示的是用户的$user_id
	 */
	public function session() {
		
		return Session::get($this->auth_key);
	}

	/**
	 * 检查用户是否允许访问当前模块
	 * 执行$this->check()后有效，可参照$this->auth_pass属性
	 *
	 * @return bealoon
	 */
	public function pass() {

		return $this->auth_pass;
	}

	/**
	 * 如果用户没有通行权，则进行什么操作
	 *
	 * @return viod
	 */
	public function error($msg, $url) {

		\Redirect::error($msg, $url);
	}

	/**
	 * 将当前的分组名/模块名/操作名规则匹配$this->adapter内的列表
	 * 从操作名开始逐序匹配，若在adapter列表内不存在该操作名，则查询模块名，
	 * 依然不存在则查询分组名，若分组名也无规则，则返回默认的false值
	 *
	 * 在adapter规则内可以使用如下格式'Home/Account/login' => array(true, 'function')
	 * 如果符合这个格式，则执行'function'()，执行返回结构为true时，则按照规则true或false执行
	 *
	 * @return bealoon true表示允许用户访问, false表示不允许用户访问
	 */
	protected function access() {

		if(empty($adapter)) $this->setAdapter();

		$routers = array(
			GROUP_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME,
			GROUP_NAME . '/' . CONTROLLER_NAME,
			GROUP_NAME
		);

		foreach ($routers as $router) {
			
			$access = $this->adapter[$router];

			// 数组的话，执行指定类方法
			if(is_array($access)) {

				if($this->$access[1]()) return $access[0];
			}

			// 布尔值的话，返回
			if(is_bool($access)) {

				return $access;
			}
		}

		return false;
	}

	/**
	 * 设置adapter适配器
	 * 可重新定义适配器的计算方式可以拓展Auth功能
	 *
	 * @return array 适配器列表
	 */
	protected function setAdapter() {

		// 查询session是否存在
		if($this->logined()) {

			$this->adapter = $this->rules[1];
		}

		// 不存在，未登录
		else {

			$this->adapter = $this->rules[0];
		}
	}
}