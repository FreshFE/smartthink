<?php
namespace Think;

class Http {

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	public static function get_client_ip($type = 0)
	{
	    static $ip;

	    $type = $type ? 1 : 0;

	    if(!is_null($ip)) {
	    	return $ip[$type];
	    }

	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	        $pos    =   array_search('unknown',$arr);
	        if(false !== $pos) unset($arr[$pos]);
	        $ip     =   trim($arr[0]);
	    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
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
	 * 判断是否SSL协议
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

	/**
	 * 404处理 
	 * 调试模式会抛异常 
	 * 部署模式下面传入url参数可以指定跳转页面，否则发送404信息
	 * @param string $msg 提示信息
	 * @param string $url 跳转URL地址
	 * @return void
	 */
	public static function _404($msg='',$url='') {
	    APP_DEBUG && Debug::throw_exception($msg);
	    if($msg && C('LOG_EXCEPTION_RECORD')) Log::write($msg);
	    if(empty($url) && C('URL_404_REDIRECT')) {
	        $url    =   C('URL_404_REDIRECT');
	    }
	    if($url) {
	        redirect($url);
	    }else{
	        static::send_http_status(404);
	        exit;
	    }
	}

}