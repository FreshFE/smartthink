<?php namespace Think;

class Auth
{
	/**
	 * auth对象容器
	 *
	 * @var object
	 */
	protected static $storage;

	/**
	 * 将auth对象传递给静态auth类，初始化auth静态类
	 *
	 * @param object $object
	 * @return object
	 */
	public static function init($object)
	{
		return static::$storage = $object;
	}

	/**
	 * 静态魔法方法
	 * 帮助调用auth对象内的方法
	 * 
	 * @param string $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		return static::$storage->$name($arguments);
	}
}