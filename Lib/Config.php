<?php namespace Think;
/**
 * SmartThink PHP
 * Copyright (c) 2004-2013 Methink
 * Thanks for ThinkPHP & GEM-MIS
 * @copyright     Copyright (c) Methink
 * @link          http://smartthink.org
 * @package       Think.Config
 * @since         SmartThink 1.0.0 & ThinkPHP 1.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * 框架常被用到的类
 * 用于获取和设置系统和项目配置
 * 由ThinkPHP的C函数演变而来
 */
class Config
{
	/**
	 * 配置静态存储
	 *
	 * @var array
	 */
	private static $storage = array();

	/**
	 * 设置一个配置
	 *
	 * @stability: 3, 接口已经稳定，但是现实方法会改变，逐步删除二维数组设置和获取支持
	 * @param string $name 配置名称
	 * @param mix $value 配置值
	 * @return mix
	 */
	public static function set($name, $value = null) {

		if(is_array($name)) {
			return static::setAll($name);
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
	 * @stability: 3, 接口已经稳定，但是现实方法会改变，逐步删除二维数组设置和获取支持
	 * @param string $name 配置名称
	 * @return mix
	 */
	public static function get($name) {
		
		if (!strpos($name, '.')) {

			$name = strtolower($name);
		    return isset(static::$storage[$name]) ? static::$storage[$name] : null;
		}
		// 二维数组设置和获取支持
		else {
			$name = explode('.', $name);
			$name[0]   =  strtolower($name[0]);
			return isset(static::$storage[$name[0]][$name[1]]) ? static::$storage[$name[0]][$name[1]] : null;
		}
	}

	/**
	 * 根据数组批量设置
	 *
	 * @stability: 4
	 * @param array $array 配置数组
	 * @return array
	 */
	public static function setAll($array) {

		return Config::$storage = array_merge(Config::$storage, array_change_key_case($array));
	}

	/**
	 * 获得所有的配置列表
	 *
	 * @stability: 4
	 * @return array
	 */
	public static function getAll() {

		return static::$storage;
	}
}