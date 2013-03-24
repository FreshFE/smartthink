<?php

class Config {

	/**
	 * 配置静态存储
	 *
	 * var array
	 */
	private static $storage = array();

	/**
	 * 设置一个配置
	 *
	 * @param string $name 配置名称
	 * @param mix $value 配置值
	 *
	 * @return mix
	 */
	public static function set($name, $value) {

		if(is_array($name)) {
			return static::lots($name);
		}

		// 字符串
		if (!strpos($name, '.')) {
			$name = strtolower($name);
		    static::$storage[$name] = $value;
		    return $value;
		}
		// 二维数组设置和获取支持
		else {
			$name = explode('.', $name);
			$name[0]   =  strtolower($name[0]);
			static::$storage[$name[0]][$name[1]] = $value;
			return $value;
		}

		
	}

	/**
	 * 得到一个配置
	 *
	 * @param string $name 配置名称
	 *
	 * @return mix
	 */
	public static function get($name) {
			
		$value = static::parse_multi($name);
		return isset($value) ? $value : null;
	}

	private static function parse_multi($name) {
		
		if (!strpos($name, '.')) {
			$name = strtolower($name);
		    return static::$storage[$name];
		}
		// 二维数组设置和获取支持
		else {
			$name = explode('.', $name);
			$name[0]   =  strtolower($name[0]);
			return static::$storage[$name[0]][$name[1]];
		}
	}

	/**
	 * 根据数组批量设置
	 *
	 * @param array $array 配置数组
	 *
	 * @return array
	 */
	public static function setAll($array) {

		return Config::$storage = array_merge(Config::$storage, array_change_key_case($array));
	}

	/**
	 * 获得所有的配置列表
	 *
	 * @return array
	 */
	public static function getAll() {

		return static::$storage;
	}
}