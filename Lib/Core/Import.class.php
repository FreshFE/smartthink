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
		

		// 生成路径
		$classfile = $base . $class . $ext;
		$classfile = realpath($classfile);
		
		// 存在该类文件，并该文件没有被加载过
		if($classfile && !static::$file[$classfile]) {

	        // 加入缓存
	        static::$file[$classfile] = true;
	        return static::require_cache($classfile);
		}

		return false;
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
	public static function require_cache($filename) {

	    static $_importFiles = array();

	    if(!isset($_importFiles[$filename])) {

	    	// 未载入过，载入
	        if(static::file_exists_case($filename)) {

	            require $filename;
	            $_importFiles[$filename] = true;
	        }

	        // 已经载入过
	        else {
	            $_importFiles[$filename] = false;
	        }
	    }

	    return $_importFiles[$filename];
	}

	/**
	 * 批量导入
	 */
	public static function require_array($array, $return = false) {

	    foreach ($array as $file) {
	        if (static::require_cache($file) && $return) return true;
	    }

	    if($return) return false;
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
	protected static function file_exists_case($filename) {

	    if (is_file($filename)) {
	        if (IS_WIN && C('APP_FILE_CASE')) {
	            if (basename(realpath($filename)) != basename($filename))
	                return false;
	        }
	        return true;
	    }

	    return false;
	}
}