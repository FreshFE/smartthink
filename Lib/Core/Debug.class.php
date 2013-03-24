<?php

class Debug {

	/**
	 * 添加和获取页面Trace记录
	 * @param string $value 变量
	 * @param string $label 标签
	 * @param string $level 日志级别 
	 * @param boolean $record 是否记录日志
	 * @return void
	 */
	public static function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
	    static $_trace =  array();
	    if('[think]' === $value){ // 获取trace信息
	        return $_trace;
	    }else{
	        $info   =   ($label?$label.':':'').print_r($value,true);
	        if('ERR' == $level && C('TRACE_EXCEPTION')) {// 抛出异常
	            static::throw_exception($info);
	        }
	        $level  =   strtoupper($level);
	        if(!isset($_trace[$level])) {
	                $_trace[$level] =   array();
	            }
	        $_trace[$level][]   = $info;
	        if((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE')  || $record) {
	            Log::record($info,$level,$record);
	        }
	    }
	}

	/**
	 * 错误输出
	 * @param mixed $error 错误
	 * @return void
	 */
	public static function halt($error) {
	    $e = array();
	    if (APP_DEBUG) {
	        //调试模式下输出错误信息
	        if (!is_array($error)) {
	            $trace          = debug_backtrace();
	            $e['message']   = $error;
	            $e['file']      = $trace[0]['file'];
	            $e['class']     = isset($trace[0]['class'])?$trace[0]['class']:'';
	            $e['function']  = isset($trace[0]['function'])?$trace[0]['function']:'';
	            $e['line']      = $trace[0]['line'];
	            $traceInfo      = '';
	            $time = date('y-m-d H:i:m');
	            foreach ($trace as $t) {
	                $traceInfo .= '[' . $time . '] ' . $t['file'] . ' (' . $t['line'] . ') ';
	                $traceInfo .= $t['class'] . $t['type'] . $t['function'] . '(';
	                $traceInfo .= implode(', ', $t['args']);
	                $traceInfo .=')<br/>';
	            }
	            $e['trace']     = $traceInfo;
	        } else {
	            $e              = $error;
	        }
	    } else {
	        //否则定向到错误页面
	        $error_page         = C('ERROR_PAGE');
	        if (!empty($error_page)) {
	            redirect($error_page);
	        } else {
	            if (C('SHOW_ERROR_MSG'))
	                $e['message'] = is_array($error) ? $error['message'] : $error;
	            else
	                $e['message'] = C('ERROR_MESSAGE');
	        }
	    }
	    // 包含异常页面模板
	    include C('TMPL_EXCEPTION_FILE');
	    exit;
	}

	/**
	 * 自定义异常处理
	 * @param string $msg 异常消息
	 * @param string $type 异常类型 默认为ThinkException
	 * @param integer $code 异常代码 默认为0
	 * @return void
	 */
	public static function throw_exception($msg, $type='ThinkException', $code=0) {
	    if (class_exists($type, false))
	        throw new $type($msg, $code, true);
	    else
	        static::halt($msg);        // 异常类型不存在则输出错误信息字串
	}

	/**
	 * 浏览器友好的变量输出
	 * @param mixed $var 变量
	 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
	 * @param string $label 标签 默认为空
	 * @param boolean $strict 是否严谨 默认为true
	 * @return void|string
	 */
	public static function dump($var, $echo=true, $label=null, $strict=true) {
	    $label = ($label === null) ? '' : rtrim($label) . ' ';
	    if (!$strict) {
	        if (ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        } else {
	            $output = $label . print_r($var, true);
	        }
	    } else {
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
	    }else
	        return $output;
	}
}