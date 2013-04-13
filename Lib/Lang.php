<?php
namespace Think;
/**
 * Core/Lang.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Core.Lang
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

class Lang {

	/**
	 * 存储语言包
	 *
	 * var array
	 */
	private static $storage = array();

	/**
	 * 获取语言包
	 *
	 * @param string $name Lang的key名
	 *
	 * @return string 返回Lang的值，不存在则返回Lang的key名
	 */
	public static function get($name) {

		// 大写
		$name = strtoupper($name);
		return isset(static::$storage[$name]) ? static::$storage[$name] : $name;
	}

	/**
	 * 设置语言包
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $string
	 */
	public static function set($name, $value) {

		if(is_array($name)) {
			static::setAll($name);
		}

		// 大写
		$name = strtoupper($name);

		// 定义语言
		return static::$storage[$name] = $value;
	}

	/**
	 * 批量设置语言包
	 *
	 * @param array $array 语言包数组
	 *
	 * @return array
	 */
	public static function setAll($array) {
		return static::$storage = array_merge(static::$storage, array_change_key_case($array, CASE_UPPER));
	}

	/**
	 * 得到所有的语言包数组
	 *
	 * @return array
	 */
	public static function getAll() {
		return static::$storage;
	}
}