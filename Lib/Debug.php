<?php
namespace Think;

use \Exception;

class Debug {

	/**
	 * 添加和获取页面Trace记录
	 *
	 * @param string $value 变量
	 * @param string $label 标签
	 * @param string $level 日志级别 
	 * @param boolean $record 是否记录日志
	 *
	 * @return void
	 */
	public static function trace($value = '[meSmart]', $label = '', $level = 'DEBUG', $record = false)
	{

	    static $_trace =  array();

	    // 获取trace信息
	    if('[meSmart]' === $value) {
	        return $_trace;
	    }
	    else {
	    	
	        $info = ($label ? $label . ':' : '') . print_r($value, true);

	        // 抛出异常
	        if('ERR' == $level && C('TRACE_EXCEPTION')) {
	            static::throw_exception($info);
	        }

	        $level = strtoupper($level);

	        if(!isset($_trace[$level])) {
                $_trace[$level] = array();
            }

	        $_trace[$level][] = $info;

	        if((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE')  || $record) {
	            Log::record($info,$level,$record);
	        }
	    }
	}

	/**
	 * 错误输出
	 *
	 * @param mixed $error 错误
	 *
	 * @return void
	 */
	public static function halt($error)
	{
		dump($error);
		exit();
	}

	/**
	 * 自定义异常处理
	 *
	 * @param string $msg 异常消息
	 * @param string $type 异常类型 默认为ThinkException
	 * @param integer $code 异常代码 默认为0
	 *
	 * @return void
	 */
	public static function throw_exception($msg, $type = 'meSmart\\Exception', $code = 0)
	{
		// throw new Exception($msg, $code);

	    if(class_exists($type, false)) {
	    	throw new $type($msg, $code, true);
	    }
	    // 异常类型不存在则输出错误信息字串
	    else {
	    	static::halt($msg);
	    }
	}

	/**
	 * 浏览器友好的变量输出
	 *
	 * @param mixed $var 变量
	 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
	 * @param string $label 标签 默认为空
	 * @param boolean $strict 是否严谨 默认为true
	 *
	 * @return void|string
	 */
	public static function dump($var, $echo = true, $label = null, $strict = true)
	{
	    $label = ($label === null) ? '' : rtrim($label) . ' ';

	    if (!$strict) {
	        if(ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	        else {
	            $output = $label . print_r($var, true);
	        }
	    }
	    else {
	        ob_start();
	        var_dump($var);
	        $output = ob_get_clean();
	        if (!extension_loaded('xdebug')) {
	            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	    }
	    if ($echo) {
	        echo($output);
	        return null;
	    }
	    else {
	    	return $output;
	    }
	}

	/**
	 * 记录和统计时间（微秒）和内存使用情况
	 *
	 * <code>
	 * Debug::mark('begin'); // 记录开始标记位
	 * // ... 区间运行代码
	 * Debug::mark('end'); // 记录结束标签位
	 * echo Debug::mark('begin','end',6); // 统计区间运行时间 精确到小数后6位
	 * echo Debug::mark('begin','end','m'); // 统计区间内存使用情况
	 * 如果end标记位没有定义，则会自动以当前作为标记位
	 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
	 * </code>
	 *
	 * @param string $start 开始标签
	 * @param string $end 结束标签
	 * @param integer|string $dec 小数位或者m
	 *
	 * @return mixed
	 */
	public static function mark($start, $end = '', $dec = 4)
	{
	    static $_info       =   array();
	    static $_mem        =   array();

	    // 记录时间
	    if(is_float($end)) {
	        $_info[$start] = $end;
	    }
	    // 统计时间和内存使用
	    elseif(!empty($end)) {

	        if(!isset($_info[$end])) {
	        	$_info[$end] = microtime(true);
	        }
	        if(MEMORY_LIMIT_ON && $dec == 'm') {
	            if(!isset($_mem[$end])) {
	            	$_mem[$end] = memory_get_usage();
	            }
	            return number_format(($_mem[$end]-$_mem[$start])/1024);          
	        }
	        else {
	            return number_format(($_info[$end]-$_info[$start]),$dec);
	        }       
	            
	    }
	    // 记录时间和内存使用
	    else{
	        $_info[$start] = microtime(true);
	        if(MEMORY_LIMIT_ON) $_mem[$start] = memory_get_usage();
	    }
	}

	/**
	 * 设置和获取统计数据
	 *
	 * <code>
	 * Debug::record('db',1); // 记录数据库操作次数
	 * Debug::record('read',1); // 记录读取次数
	 * echo Debug::record('db'); // 获取当前页面数据库的所有操作次数
	 * echo Debug::record('read'); // 获取当前页面读取次数
	 * </code>
	 *
	 * @param string $key 标识位置
	 * @param integer $step 步进值
	 *
	 * @return mixed
	 */
	public static function record($key, $step = 0, $save = false)
	{
	    static $_num = array();

	    if(!isset($_num[$key])) {
	        $_num[$key] = (false !== $save) ? S('N_'.$key) :  0;
	    }

	    if(empty($step)) {
	    	return $_num[$key];
	    }
	    else {
	    	$_num[$key] = $_num[$key] + (int) $step;
	    }

	    // 保存结果
	    if(false !== $save) {
	        S('N_'.$key, $_num[$key], $save);
	    }
	}

	public static function output(Exception $error)
	{
		// echo $error->getMessage();
		
		include Config::get('TMPL_EXCEPTION_FILE');
		exit();
	}
}