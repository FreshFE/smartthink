<?php

class Redirect {

	/**
	 */
	static public function succees(string $message, string $jumpUrl) {

		Redirect::to($message, true, $jumpUrl);
	}

	/**
	 */
	static public function error(string $message, string $jumpUrl) {

		Redirect::to($message, false, $jumpUrl);
	}

	/**
	 */
	static public function to(string $message, bealoon $status, string $jumpUrl) {

	    // 设置路径
	    if(!$jumpUrl) {
	    	$jumpUrl = empty($_SERVER["HTTP_REFERER"]) ? __APP__ : $_SERVER["HTTP_REFERER"];
	    }

	    // 设置提示信息session
	    // 写入两个session，分别是提示信息和操作状态
	    if(C('JUMP_SESSION')) {
	        session(C('JUMP_SESSION_INFO'), $message);
	        session(C('JUMP_SESSION_STATUS'), $status);
	    }

	    // 重定向
	    Redirect::send($jumpUrl);
	}

	/**
	 */
	static private function send($url) {

		$url = str_replace(array("\n", "\r"), '', $url);

	    if (!headers_sent()) {
	        header('Location: ' . $url);
	        exit();
	    } else {
	    	exit('跳转失败');
	    }
	}
}