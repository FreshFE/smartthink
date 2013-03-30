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
	public static function uses($class, $alias = '', $ext = EXT) {

		throw new Exception("Close uses", 1);
		exit();

		// 解析路径
		if($alias != '') {
			$alias = static::parse_alias($alias);
			$alias = static::parse_uri_suffix($alias);
		}

		// 载入
		$filename = $alias . $class . $ext;
		return static::load($filename);
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

	/**
	 * 批量加载文件
	 * 内部批量调用load方法
	 *
	 * @param array $files 文件名数组
	 * @param bealoon $return 是否需要返回 !!!继续研究是否有存在该参数和功能的必要
	 *
	 * @return bealoon
	 */
	public static function loads($files) {

	    foreach ($files as $file) {
	        static::load($file);
	    }
	}

	/**
	 * 检查该文件是否存在大小写版本
	 * 针对windows平台做优化
	 *
	 * @param string $filename 检查的文件路径
	 * @return bealoon
	 */
	public static function file_exists_case($filename) {

		// 该文件是否存在
	    if (is_file($filename)) {

	    	// windows平台下并开启大小写检查
	        if (IS_WIN && C('APP_FILE_CASE')) {
	            if (basename(realpath($filename)) != basename($filename)) {
	            	return false;
	            }
	        }
	        return true;
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
}