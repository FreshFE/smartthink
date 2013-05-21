<?php
namespace Think;

class Request
{
	/**
	 * 全局存储器
	 * 专门用于储存在请求时需要保存的全局变量
	 *
	 * @var array
	 */
	public static $storage = array();

	/**
	 * 得到存储器内的数据
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function getStorage($name = null)
	{
		if(is_null($name)) {
			return static::$storage;
		}

		return static::$storage[$name];
	}

	/**
	 * 设置存储器内的数据
	 * 如果储存器内已经有该数据，需要强制才能替换
	 *
	 * @param string $name
	 * @param mixed $value 
	 * @param boolean $force 是否强制写入
	 * @return mixed
	 */
	public static function setStorage($name, $value, $force = false)
	{
		$storage = static::$storage;

		if(isset($storage[$name]) && !$force) {
			return false;
		}

		return static::$storage[$name] = $value;
	}

	/**
	 * 检查当前请求方法和设定的方法是否一致
	 *
	 * @param string $method
	 * @return boolean
	 */
	public static function is($method)
	{
		return strtolower($_SERVER['REQUEST_METHOD']) == $method ? true : false;
	}

	/**
	 * 得到$_GET内的值
	 * 该方法主要用于过滤$_GET值
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function query($name)
	{
		return isset($_GET[$name]) ? $_GET[$name] : null;
	}

	/**
	 * 得到$_POST内的值
	 * 该方法主要用于过滤$_POST值
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function post($name)
	{
		return isset($_POST[$name]) ? $_POST[$name] : null;
	}

	/**
	 * 获取客户端IP地址
	 *
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	public static function ip($type = false)
	{
		static $ip;

		// 类型
		$type = $type ? 1 : 0;

		// 缓存
		if(!is_null($ip)) {
			return $ip[$type];
		}

		// 获得IP
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

		    $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		    $pos = array_search('unknown',$arr);
		    if($pos !== false) {
		    	unset($arr[$pos]);
		    }
		    $ip = trim($arr[0]);
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {

		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(isset($_SERVER['REMOTE_ADDR'])) {

		    $ip = $_SERVER['REMOTE_ADDR'];
		}

		// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);

		return $ip[$type];
	}

	/**
	 * 判断是否SSL协议
	 *
	 * @return boolean
	 */
	public static function is_ssl()
	{
	    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
	        return true;
	    }
	    elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
	        return true;
	    }

	    return false;
	}
}