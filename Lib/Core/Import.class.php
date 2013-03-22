<?php

class Import {

	/**
	 * 静态变量，存放文件路径
	 *
	 * var array
	 */
	public static $file = array();

	/**
	 * 别名映射表
	 *
	 * var array
	 */
	public static $alias = array(

		'Core' => CORE_PATH,
		'App' => APP_PATH,
		'Extend' => EXTEND_PATH
	);

	/**
	 * 缓存加载类
	 *
	 * @param string $class 类名
	 * @param string $base 别名或路径
	 * @param string $ext 后缀
	 *
	 * @return bealoon
	 */
	public static function uses(string $class, string $base, $ext = '.class.php') {

		// 解析路径
		$base = static::parse_alias($base);
		$base = static::parse_uri_suffix($base);

		// 载入
		$filename = $base . $class . $ext;
		return static::load($filename);
	}

	/**
	 * 解析uri是否存在'/'后缀，不存在则添加
	 *
	 * @param string $uri
	 *
	 * @return string
	 */
	protected static function parse_uri_suffix($uri) {

		// 解析地址后缀'/'
		if(substr($uri, -1) != '/') {
		    $uri .= '/';
		}

		return $uri;
	}

	/**
	 * 解析别名映射表
	 *
	 * @param string $alias
	 *
	 * @return string
	 */
	protected static function parse_alias($alias) {

		// 遍历查找别名映射表
		foreach (static::$alias as $key => $value) {
			if($key == $alias) {
				$alias = $value;
			}
		}

		return $alias;
	}

	// TODO: 重构
	/**
	 * 带缓存导入
	 */
	public static function load($filename) {

	    static $_files = array();
	    
	    $filename = realpath($filename);

	    if(!isset($_files[$filename])) {

	    	// 未载入过，载入
	        if(static::file_exists_case($filename)) {

	            require $filename;
	            $_files[$filename] = true;
	        }

	        // 已经载入过
	        else {
	            $_files[$filename] = false;
	        }
	    }

	    return $_files[$filename];
	}

	public static function require_cache($filename) {
		return static::load($filename);
	}

	/**
	 * 批量导入
	 */
	public static function require_array($array, $return = false) {

	    foreach ($array as $file) {
	        if(static::load($file) && $return) {
	        	return true;
	        }
	    }

	    if($return) {
	    	return false;
	    }
	}

	public static function alias_import($alias, $classfile='') {
	    static $_alias = array();
	    if (is_string($alias)) {
	        if(isset($_alias[$alias])) {
	            return static::require_cache($_alias[$alias]);
	        }elseif ('' !== $classfile) {
	            // 定义别名导入
	            $_alias[$alias] = $classfile;
	            return;
	        }
	    }elseif (is_array($alias)) {
	        $_alias   =  array_merge($_alias,$alias);
	        return;
	    }
	    return false;
	}

	// TODO: 重构
	public static function file_exists_case($filename) {

	    if (is_file($filename)) {
	        if (IS_WIN && C('APP_FILE_CASE')) {
	            if (basename(realpath($filename)) != basename($filename))
	                return false;
	        }
	        return true;
	    }

	    return false;
	}

	/**
	 * 导入所需的类库 同java的Import 本函数有缓存功能
	 * @param string $class 类库命名空间字符串
	 *  @|APP_NAME表示该项目的lib
	 *  think表示框架内的lib
	 *  org|com表示extend/library下项目
	 *  other表示其他项目
	 * @param string $baseUrl 起始路径
	 * @param string $ext 导入的文件扩展名
	 * @return boolean
	 */
	public static function old($class, $baseUrl = '', $ext = '.class.php') {

	    // 已载入库列表
	    static $_file = array();

	    // 转义
	    $class = str_replace(array('.', '#'), array('/', '.'), $class);

	    // 检查别名导入
	    if('' === $baseUrl && false === strpos($class, '/')) {
	        return static::alias_import($class);
	    }

	    // 检查是否已经载入
	    if (isset($_file[$class . $baseUrl])) {
	        return true;
	    }
	    // 添加到载入列表
	    else {
	        $_file[$class . $baseUrl] = true;
	    }

	    // 解析$class
	    $class_strut = explode('/', $class);

	    // 如果$baseUrl为空则解析$class
	    if(empty($baseUrl)) {

	        $libPath = defined('BASE_LIB_PATH') ? BASE_LIB_PATH : LIB_PATH;

	        // 加载当前项目应用类库
	        if('@' == $class_strut[0] || APP_NAME == $class_strut[0]) {
	            
	            $baseUrl = dirname($libPath);
	            $class   = substr_replace($class, basename($libPath) . '/', 0, strlen($class_strut[0]) + 1);
	        }

	        // think 官方基类库
	        elseif('think' == strtolower($class_strut[0])) {
	            $baseUrl = CORE_PATH;
	            $class   = substr($class,6);
	        }

	        // org 第三方公共类库 com 企业公共类库
	        elseif(in_array(strtolower($class_strut[0]), array('org', 'com'))) {

	            $baseUrl = LIBRARY_PATH;
	        }

	        // 加载其他项目应用类库
	        else {
	            $class = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
	            $baseUrl = APP_PATH . '../' . $class_strut[0] . '/'.basename($libPath).'/';
	        }
	    }

	    // 分析后缀
	    if(substr($baseUrl, -1) != '/') {
	        $baseUrl .= '/';
	    }

	    // 合并classfile
	    $classfile = $baseUrl . $class . $ext;

	    // 如果类不存在 则导入类库文件
	    if (!class_exists(basename($class),false)) {
	        return static::load($classfile);
	    }
	}
}