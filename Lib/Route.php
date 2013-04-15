<?php namespace Think;
/**
 * SmartThink PHP
 * Copyright (c) 2004-2013 Methink
 * Thanks for ThinkPHP & GEM-MIS
 * @copyright     Copyright (c) Methink
 * @link          http://smartthink.org
 * @package       Think.Route
 * @since         SmartThink 1.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * 在执行了Dispatch后定义了常量GROUP_NAME
 * 之后便在分组项目中开发本路由
 * TODO：分离出路由驱动和路由行为
 */
class Route
{
	/**
	 * 初始化方法
	 */
	public static function init()
	{
		$controller = static::get_controller_method();

		define('CONTROLLER_NAME', 	$controller[0]);
        define('ACTION_NAME', 		$controller[1]);
		define('__URL__', 			__GROUP__ . '/' . strtolower(CONTROLLER_NAME));
		define('__ACTION__', 		__URL__ . '/' . strtolower(ACTION_NAME));

		// dump([
		// 	'CONTROLLER_NAME' => CONTROLLER_NAME,
		// 	'ACTION_NAME' => ACTION_NAME,
		// 	'__URL__' => __URL__,
		// 	'__ACTION__' => __ACTION__,
		// ]);

		static::set_query();
	}

	/**
	 * 根据pathinfo得到控制器名和相关的方法
	 */
	public static function get_controller_method()
	{
		$paths = Dispatch::$paths;
		$count = count($paths);

		if($count === 0)
		{
			return array('Index', 'index');
		}
		else if($count === 1)
		{
			return array($paths[0], 'index');
		}
		else {
			return array($paths[0], $paths[1]);
		}
	}

	/**
	 * 解析pathinfo内剩余的请求
	 * id/1 => $_GET['id'] = 1
	 *
	 * @param array $pathinfo
	 * @return array
	 */
	private static function set_query() {

		$paths = Dispatch::$paths;
		$paths = array_splice($paths, 2);
		$request = array();

		// 奇数项赋值给偶数项
		foreach ($paths as $key => $value) {
			if($key % 2) {
				$request[$paths[$key - 1]] = $value;
			}
		}

		// 合并到GET
		return $_GET = array_merge($_GET, $request);
	}
}