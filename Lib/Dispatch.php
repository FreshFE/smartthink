<?php namespace Think;
/**
 * SmartThink PHP
 * Copyright (c) 2004-2013 Methink
 * Thanks for ThinkPHP & GEM-MIS
 * @copyright     Copyright (c) Methink
 * @link          http://smartthink.org
 * @package       Think.Dispatch
 * @since         SmartThink 1.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * 根据项目设置来分配到分组目录下
 * 可以设置subdomain和pathinfo两种方式
 */
class Dispatch
{
	/**
	 * 当前分组的类型
	 * subdomain|pathinfo|default
	 *
	 * @var string
	 */
	public static $type;

	/**
	 * 当前的分组名称
	 *
	 * @var string
	 */
	public static $group;

	/**
	 * 在去掉分组名的情况下，将pathinfo解析放入数组
	 *
	 * @var array
	 */
	public static $paths = array();

	/**
	 * 分组名称配置方法
	 * 可以在项目目录(APP_PATH)下的dispatch.php配置
	 *
	 * @var array
	 */
	private static $storage = array(
		'default' => 'Home',
		'subdomain' => '',
		'pathinfo' => ''
	);

	/**
	 * 设置$this->storage的方法
	 */
	public static function set($name, $value = null)
	{
		if(is_array($name))
		{
			static::$storage = array_merge(static::$storage, $name);
		}
		else {
			$storage = static::$storage;
			return $storage[$name] = $value;
		}
	}

	/**
	 * 初始化，最关键的对外运行接口
	 */
	public static function init()
	{
		// 加载配置文件
		if(is_file(APP_PATH . 'dispatch.php'))
		{
			static::set(include APP_PATH . 'dispatch.php');
		}

		// 解析subdomain
		if($group = static::subdomain())
		{
			static::$type = 'subdomain';
			static::$group = $group;
			static::set_paths();
		}
		// 解析pathinfo
		else if($group = static::pathinfo())
		{
			static::$type = 'pathinfo';
			static::$group = $group;
			$pathinfo = str_replace('/' . strtolower($group), '', $_SERVER['PATH_INFO']);
			static::set_paths($pathinfo);
		}
		// 默认情况
		else
		{
			static::$type = 'default';
			static::$group = static::$storage['default'];
			static::set_paths();
		}

		static::define_const();
	}

	/**
	 * 解析伪后缀名称
	 */
	protected static function extension()
	{
		$name = $_SERVER['PATH_INFO'];
		$exts = explode('.', $name);

		if(count($exts) >= 2)
		{
			return $exts[count($exts) - 1];
		}
		else {
			return '';
		}
	}

	/**
	 * 创建分组名称，用于__GROUP__常量的定义
	 */
	protected static function build_group()
	{
		if(static::$type == 'pathinfo')
		{
			return (INDEX_FILE ? '/' . INDEX_FILE : '' ) . '/' . GROUP_NAME;
		}
		else if(static::$type == 'subdomain')
		{
			return '//' . $_SERVER['HTTP_HOST'] . '/' . (INDEX_FILE ? INDEX_FILE : '' );
		}
		else
		{
			return (INDEX_FILE ? '/' . INDEX_FILE : '' );
		}
	}

	/**
	 * 定义常量
	 */
	protected static function define_const()
	{
		// 常量定义
		define('GROUP_NAME', 		static::$group);
		define('GROUP_PATH', 		LIB_PATH . GROUP_NAME . '/');
        define('__EXT__', 			strtolower(static::extension()));
        define('__SELF__', 			strip_tags($_SERVER['REQUEST_URI']));
        define('__GROUP__', 		strtolower(static::build_group()));

		// dump([
		// 	'GROUP_NAME' => GROUP_NAME,
		// 	'GROUP_PATH' => GROUP_PATH,
		// 	'__EXT__' => __EXT__,
		// 	'__SELF__' => __SELF__,
		// 	'__GROUP__' => __GROUP__,
		// ]);
	}

	/**
	 * 设置paths
	 */
	protected static function set_paths($name = null)
	{
		if(is_null($name))
		{
			$name = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		}

		if(!empty($name)) {
			$paths = trim($name, '/');
			static::$paths = explode('/', $paths);
		}
	}

	/**
	 * 解析二级域名的部署
	 */
	protected static function subdomain()
	{
		$name = $_SERVER['SERVER_NAME'];
		$subs = explode(',', static::$storage['subdomain']);

		foreach ($subs as $key => $sub)
		{
			if(strpos($name, strtolower($sub)) === 0)
			{
				return $sub;
			}
		}

		return false;
	}

	/**
	 * 解析pathinfo的部署
	 */
	protected static function pathinfo()
	{
		$name = $_SERVER['PATH_INFO'];
		$subs = explode(',', static::$storage['pathinfo']);

		foreach ($subs as $key => $sub)
		{
			$tsub = '/' . strtolower($sub);
			if(strpos($name, $tsub) === 0)
			{
				return $sub;
			}
		}

		return false;
	}
}