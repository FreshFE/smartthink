<?php
namespace Think;
/**
 * Core/Router.class.php
 * MeSmart php
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/MeSmart
 * @package       Core.Router
 * @since         MeSmart php 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * Router Class
 * 解析url请求，分配到各个controller
 */
class Router {

	/**
	 * 解析$_SERVER请求信息
	 * 分析pathinfo，将pathinfo映射到controller
	 *
	 * @return void
	 */
	public static function dispatch() {

		// 获得并解析
		$pathinfo = strip_tags($_SERVER['PATH_INFO']);
		$pathinfo = explode('.', $pathinfo);

		// 定义后缀
		if(count($pathinfo) == 2) {
			$extension = $pathinfo[1];
		}
		else {
			$extension = '';
		}
		
		// 主pathinfo
		$pathinfo = $pathinfo[0];

		// TODO: 匹配二级域名
		// TODO: 匹配路由

		// 分析module和action
		$pathinfo = explode('/', trim($pathinfo, '/'));
		$controller = static::getControllerName($pathinfo);
		
		// 常量定义
		define('GROUP_NAME', 		$controller['group']);
		define('GROUP_PATH', 		LIB_PATH . GROUP_NAME . '/');
		define('CONTROLLER_NAME', 	$controller['module']);
        define('ACTION_NAME', 		$controller['action']);

        define('__EXT__', 			$extension);
        define('__SELF__', 			strip_tags($_SERVER['REQUEST_URI']));
		define('__APP__', 			APP_NAME);
        define('__GROUP__', 		strtolower(__APP__ . '/' . GROUP_NAME));
        define('__URL__', 			__GROUP__ . '/' . strtolower(CONTROLLER_NAME));
        define('__ACTION__', 		__URL__ . '/' . strtolower(ACTION_NAME));

        // 解析get值
		static::parsePathinfoQuery($pathinfo);
	}

	/**
	 * 解析pathinfo内剩余的请求
	 * id/1 => $_GET['id'] = 1
	 *
	 * @param array $pathinfo
	 *
	 * @return array
	 */
	private static function parsePathinfoQuery($pathinfo) {

		// 奇数项赋值给偶数项
		foreach ($pathinfo as $key => $value) {
			if($key % 2) {
				$request[$pathinfo[$key - 1]] = $value;
			}
		}

		// 合并到GET
		return $_GET = array_merge($_GET, $request);
	}

	/**
	 * 得到控制器名称
	 *
	 * @param array &$pathinfo
	 *
	 * @return array
	 */
	private static function getControllerName(&$pathinfo) {
		return array(
			'group' => static::getGroupName($pathinfo),
			'module' => static::getModuleName($pathinfo),
			'action' => static::getActionName($pathinfo)
		);
	}

	/**
	 * 得到分组名称
	 *
	 * @param array &$pathinfo
	 *
	 * @return string
	 */
	private static function getGroupName(&$pathinfo) {

		// 转化
		$group_name = ucfirst($pathinfo[0]);
		$group_list = explode(',', APP_GROUP);

		// 如果不存在
		if(!in_array($group_name, $group_list)) {
			return 'Home';
		}
		// 如果存在
		else {
			$pathinfo = static::resetPathinfo($pathinfo, 0);
			return $group_name;
		}
	}

	/**
	 * 得到模块名称
	 *
	 * @param array &$pathinfo
	 *
	 * @return string
	 */
	private static function getModuleName(&$pathinfo) {

		if($pathinfo[0]) {
			$module_name = ucfirst($pathinfo[0]);
			$pathinfo = static::resetPathinfo($pathinfo, 0);
		}
		else {
			$module_name = 'Index';
		}

		return $module_name;
	}

	/**
	 * 得到操作名称
	 *
	 * @param array &$pathinfo
	 *
	 * @return string
	 */
	private static function getActionName(&$pathinfo) {

		if($pathinfo[0]) {
			$action_name = $pathinfo[0];
			$pathinfo = static::resetPathinfo($pathinfo, 0);
		}
		else {
			$action_name = 'index';
		}

		return $action_name;
	}

	/**
	 * 删除pathinfo数组内项，并重新生成key排序
	 *
	 * @param array &$pathinfo
	 *
	 * @return array
	 */
	private static function resetPathinfo(&$pathinfo, $key) {
		unset($pathinfo[$key]);
		return array_values($pathinfo);
	}
}