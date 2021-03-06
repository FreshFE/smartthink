<?php
namespace Think;
/**
 * Core/Redirect.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/thinkphp
 * @package       Core.Redirect
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * 帮助页面跳转的类
 */
class Redirect {

	/**
	 * 如果用户操作成功跳转
	 *
	 * @param string $message 提示信息
	 * @param string $url 跳转地址
	 *
	 * @return void
	 */
	static public function success($message, $url)
	{
		static::to($message, $url, true);
	}

	/**
	 * 如果用户操作失败跳转
	 *
	 * @param string $message 提示信息
	 * @param string $url 跳转地址
	 *
	 * @return void
	 */
	static public function error($message, $url)
	{
		static::to($message, $url, false);
	}

	/**
	 * 页面跳转
	 *
	 * @param string $message 提示信息
	 * @param bealoon $status 操作成功或失败
	 * @param string $url 跳转地址
	 *
	 * @return void
	 */
	static public function to($message, $url, $status)
	{
	    // 设置路径
	    if(!$url) {
	    	$url = empty($_SERVER["HTTP_REFERER"]) ? __GROUP__ : $_SERVER["HTTP_REFERER"];
	    }

	    // 设置提示信息session
	    // 写入两个session，分别是提示信息和操作状态
	    if(C('JUMP_SESSION_ON') && !empty($message)) {
	        Session::set(C('JUMP_SESSION_INFO'), $message);
	        Session::set(C('JUMP_SESSION_STATUS'), $status);
	    }

	    // 重定向
	    static::send($url);
	}

	/**
	 * 执行页面跳转
	 *
	 * @param string $url 跳转地址
	 *
	 * @return void
	 */
	static private function send($url)
	{
	    if(!headers_sent())
	    {
	        header('Location: ' . $url);
	    }
	    else {
	    	echo '
	    		<!DOCTYPE html>
	    		<html>
					<head>
						<meta http-equiv="refresh" content="0; url=http://'.$_SERVER['HTTP_HOST'].$url.' ">
					</head>
					<body>
					</body>
				</html>
	    	';
	    }

	    exit('跳转失败');
	}
}