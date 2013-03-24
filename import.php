<?php

class Import {

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
	public static function uses(string $class, $alias = '', $ext = '.class.php') {

		// 解析路径
		if($alias != '') {
			$alias = static::parse_alias($alias);
			$alias = static::parse_uri_suffix($alias);
		}

		// 载入
		$filename = $alias . $class . $ext;
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

	public static function alias($alias, $classfile='') {
	    
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
}