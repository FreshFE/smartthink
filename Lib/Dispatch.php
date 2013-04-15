<?php namespace Think;

class Dispatch
{
	public static $type;

	public static $group;

	public static $paths = array();

	private static $storage = array(
		'default' => 'Home',
		'subdomain' => '',
		'pathinfo' => ''
	);

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

	public static function init()
	{
		// 加载配置文件
		static::set(include APP_PATH . 'route.php');

		// 解析subdomain
		if($group = static::subdomain())
		{
			static::$type = 'subdomain';
			static::$group = $group;
			static::setPaths();
		}
		// 解析pathinfo
		else if($group = static::pathinfo())
		{
			static::$type = 'pathinfo';
			static::$group = $group;
			$pathinfo = str_replace('/' . strtolower($group), '', $_SERVER['PATH_INFO']);
			static::setPaths($pathinfo);
		}
		// 默认情况
		else
		{
			static::$type = 'default';
			static::$group = static::$storage['default'];
			static::setPaths();
		}

		static::define_const();
	}

	public static function extension()
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

	public static function build_group()
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

	public static function define_const()
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

	public static function setPaths($name = null)
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

	public static function subdomain()
	{
		$name = $_SERVER['SERVER_NAME'];
		$subs = explode('.', static::$storage['subdomain']);

		foreach ($subs as $key => $sub)
		{
			if(strpos($name, strtolower($sub)) === 0)
			{
				return $sub;
			}
		}

		return false;
	}

	public static function pathinfo()
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