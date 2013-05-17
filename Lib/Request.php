<?php
namespace Think;

class Request
{
	public static function is($method)
	{
		return strtolower($_SERVER['REQUEST_METHOD']) == $method ? true : false;
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
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
		    $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		    $pos = array_search('unknown',$arr);
		    if($pos !== false) {
		    	unset($arr[$pos]);
		    }
		    $ip = trim($arr[0]);
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP']))
		{
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(isset($_SERVER['REMOTE_ADDR']))
		{
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
	public static function is_ssl() {
	    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
	        return true;
	    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
	        return true;
	    }
	    return false;
	}

	public static function query($name)
	{
		return isset($_GET[$name]) ? $_GET[$name] : null;
	}
}