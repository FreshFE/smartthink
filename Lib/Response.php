<?php
namespace Think;

use Think\Debug as Debug;
use Think\Exception as Exception;

class Response {

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	public static function get_client_ip($type = 0)
	{
	    return Request::ip($type);
	}

	/**
	 * 发送HTTP状态
	 * @param integer $code 状态码
	 * @return void
	 */
	public static function send_http_status($code) {
	    static $_status = array(
	        // Success 2xx
	        200 => 'OK',
	        // Redirection 3xx
	        301 => 'Moved Permanently',
	        302 => 'Moved Temporarily ',  // 1.1
	        // Client Error 4xx
	        400 => 'Bad Request',
	        403 => 'Forbidden',
	        404 => 'Not Found',
	        // Server Error 5xx
	        500 => 'Internal Server Error',
	        503 => 'Service Unavailable',
	    );
	    if(isset($_status[$code])) {
	        header('HTTP/1.1 '.$code.' '.$_status[$code]);
	        // 确保FastCGI模式下正常
	        header('Status:'.$code.' '.$_status[$code]);
	    }
	}

	/**
	 * XML编码
	 * @param mixed $data 数据
	 * @param string $root 根节点名
	 * @param string $item 数字索引的子节点名
	 * @param string $attr 根节点属性
	 * @param string $id   数字索引子节点key转换的属性名
	 * @param string $encoding 数据编码
	 * @return string
	 */
	public static function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
	    if(is_array($attr)){
	        $_attr = array();
	        foreach ($attr as $key => $value) {
	            $_attr[] = "{$key}=\"{$value}\"";
	        }
	        $attr = implode(' ', $_attr);
	    }
	    $attr   = trim($attr);
	    $attr   = empty($attr) ? '' : " {$attr}";
	    $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
	    $xml   .= "<{$root}{$attr}>";
	    $xml   .= static::data_to_xml($data, $item, $id);
	    $xml   .= "</{$root}>";
	    return $xml;
	}

	/**
	 * 数据XML编码
	 * @param mixed  $data 数据
	 * @param string $item 数字索引时的节点名称
	 * @param string $id   数字索引key转换为的属性名
	 * @return string
	 */
	public static function data_to_xml($data, $item='item', $id='id') {
	    $xml = $attr = '';
	    foreach ($data as $key => $val) {
	        if(is_numeric($key)){
	            $id && $attr = " {$id}=\"{$key}\"";
	            $key  = $item;
	        }
	        $xml    .=  "<{$key}{$attr}>";
	        $xml    .=  (is_array($val) || is_object($val)) ? static::data_to_xml($val, $item, $id) : $val;
	        $xml    .=  "</{$key}>";
	    }
	    return $xml;
	}

	/**
	 * 404处理 
	 * 调试模式会抛异常 
	 * 部署模式下面传入url参数可以指定跳转页面，否则发送404信息
	 * @param string $msg 提示信息
	 * @param string $url 跳转URL地址
	 * @return void
	 */
	public static function _404($message = '', $url)
	{
		if(APP_DEBUG)
		{
			Debug::output(new Exception($message));
		}
		else {
			static::send_http_status(404);
	        exit;
		}
	}

	public static function json($array, $charset = '', $contentType = 'application/json')
	{
		// 默认字符编码
	    if(empty($charset))
	    {
	    	$charset = Config::get('DEFAULT_CHARSET');
	    }

	    // 默认文件类型
	    if(empty($contentType))
	    {
	    	$contentType = Config::get('TMPL_CONTENT_TYPE');
	    }

		// 输出header头
		header('Content-Type:'.$contentType.'; charset='.$charset);
		header('Cache-control: '.Config::get('HTTP_CACHE_CONTROL'));
		header('X-Powered-By: SmartThink');
		header('X-Thanks: Thanks for ThinkPHP, Lavarel, Smarty & Composer');
		header('X-Develop-Team: http://smartthink.org');
		exit(json_encode($array));
	}

}