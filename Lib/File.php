<?php
namespace Think;

class File {

	private static $storage = array();

	public static $path = DATA_PATH;

	public static function get($name) {

		$filename = static::parse_name($name);

		// 如果设置了内存缓存则返回
		if (isset(static::$storage[$name])) {
			return static::$storage[$name];
		}

		// 获取缓存数据
		if (is_file($filename)) {
		    $value = include $filename;
		    static::$storage[$name] = $value;
		}
		// 不存在该文件
		else {
		    $value = false;
		}

		return $value;
	}

	public static function set($name, $value) {

		$filename = static::parse_name($name);

	    // 缓存数据
	    $dir = dirname($filename);

	    // 目录不存在则创建
	    if (!is_dir($dir)) {
	    	mkdir($dir,0755,true);
	    }

	    // 放入内存缓存
	    $_cache[$name]  =   $value;
	    return file_put_contents($filename, strip_whitespace("<?php\treturn " . var_export($value, true) . ";?>"));
	}

	public static function clear($name) {

		$filename = static::parse_name($name);

		return false !== strpos($name,'*') ? array_map("unlink", glob($filename)) : unlink($filename);
	}

	private static function parse_name($name) {
		return static::$path . $name . '.php';
	}

	/**
	 * 检查该文件是否存在大小写版本
	 * 针对windows平台做优化
	 *
	 * @param string $filename 检查的文件路径
	 * @return bealoon
	 */
	public static function exists_case($filename) {

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
}