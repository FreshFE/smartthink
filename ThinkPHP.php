<?php

/**
 * 记录开始运行时间
 */
$GLOBALS['_beginTime'] = microtime(true);

/**
 * 记录内存初始使用
 */
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));

if(MEMORY_LIMIT_ON) {
	$GLOBALS['_startUseMems'] = memory_get_usage();
}

/**
 * 入口文件必须定义
 * THINK_PATH, EXTEND_PATH, APP_PATH and APP_DEBUG
 */
if(!defined('THINK_PATH') || !defined('EXTEND_PATH') || !defined('APP_PATH') || !defined('APP_DEBUG')) {
	die('No defined THINK_PATH, EXTEND_PATH, APP_PATH and APP_DEBUG');
}

/**
 * Runtime目录
 */
defined('RUNTIME_PATH') or define('RUNTIME_PATH', APP_PATH . 'Runtime/');

/**
 * 加载runtime并执行项目
 */
require THINK_PATH . 'Runtime.class.php';
Runtime::init();