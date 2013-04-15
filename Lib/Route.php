<?php namespace Think;

use Think\Import as Import;

class Route
{
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