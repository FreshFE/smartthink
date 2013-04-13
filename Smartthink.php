<?php namespace Think;
/**
 * SmartThink.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Library.Auth
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use Think\App as App;
use Think\Debug as Debug;

// -------------------------------------------
// 类自动加载
// -------------------------------------------
require_once 'autoload.php';

// -------------------------------------------
// 函数载入
// -------------------------------------------
require_once 'functions.php';

// -------------------------------------------
// 启动框架
// -------------------------------------------
class Smartthink {

    /**
     * 初始化Runtime开始执行程序
     *
     * @return void
     */
    public static function run() {

        // -------------------------------------------
        // 定义常量
        // -------------------------------------------
        static::define_const();

        // -------------------------------------------
        // 注册错误和异常
        // -------------------------------------------
        static::register_error();

        // -------------------------------------------
        // 执行runtime
        // -------------------------------------------
        static::load_runtime_file();
    }

    /**
     * 定义常量
     *
     * @return void
     */
    public static function define_const() {

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
    	 * FRAME_PATH & APP_PATH
    	 */
    	if(!defined('FRAME_PATH') || !defined('APP_PATH')) {
    		die('No defined FRAME_PATH & APP_PATH!!');
    	}

        /**
         * 框架版本
         *
         * @const VERSION
         */
        define('VERSION', '2.0.0 Beta');

        /**
         * 当前系统和操作方式
         *
         * @const bealoon IS_CGI 是否为CGI
         * @const bealoon IS_WIN 是否为windows系统
         * @const bealoon IS_CLI 是否为命令行
         */
        define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0 );
        define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
        define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

        /**
         * 项目名称
         * 可以在入口文件内定义
         *
         * @const string
         */
        // defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));
        defined('APP_NAME') or define('APP_NAME', '');

        /**
         * 基本文件路径
         * 路径设置 可在入口文件中重新定义 所有路径常量都必须以/ 结尾
         *
         * @const CORE_PATH
         * @const COMMON_PATH
         * @const LIB_PATH
         * @const CONF_PATH
         * @const LANG_PATH
         * @const TMPL_PATH
         * @const HTML_PATH
         *
         * @const LOG_PATH
         * @const TEMP_PATH
         * @const DATA_PATH
         * @const CACHE_PATH
         * @const EXT
         */
        // 删除CORE_PATH
        defined('CORE_PATH')    or define('CORE_PATH',      FRAME_PATH.'Lib/');     // 系统核心类库目录

        // 删除COMMON_PATH
        defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.'Common/');    // 项目公共目录

        // 合并LIB_PATH到APP目录
        defined('LIB_PATH')     or define('LIB_PATH',       APP_PATH.'Lib/');       // 项目类库目录

        defined('CONF_PATH')    or define('CONF_PATH',      APP_PATH.'Conf/');      // 项目配置目录
        defined('LANG_PATH')    or define('LANG_PATH',      APP_PATH.'Lang/');      // 项目语言包目录
        defined('TMPL_PATH')    or define('TMPL_PATH',      APP_PATH.'Tpl/');       // 项目模板目录
        defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.'Html/');      // 项目静态目录

        /**
         * Runtime目录
         */
        defined('RUNTIME_PATH') or define('RUNTIME_PATH',   APP_PATH . 'Runtime/');
        defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'Logs/');  // 项目日志目录
        defined('TEMP_PATH')    or define('TEMP_PATH',      RUNTIME_PATH.'Temp/');  // 项目缓存目录
        defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'Data/');  // 项目数据目录
        defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'Cache/'); // 项目模板缓存目录

        defined('EXT')          or define('EXT',            '.php');          // 项目模板缓存目录
    }

    /**
     * 加载运行时所需要的文件
     * 检查调试模式创建目录
     *
     * @return void
     */
    public static function load_runtime_file() {

        // 定义
        $files = array(            
            // TODO: 重构
            CORE_PATH.'View/Helper'.EXT
        );

        // 载入
        foreach ($files as $key => $file) {
            include $file;
        }

        // 调试模式下检查路径和文件
        if(APP_DEBUG) {
            
            // 创建项目目录结构
            if(!is_dir(LIB_PATH)) {
                Debug::throw_exception('不存在项目目录结构');
            }

            // 检查缓存目录
            if(!is_dir(CACHE_PATH)) {
                static::check_runtime();
            }
        }

        // 记录文件加载时间
        Debug::mark('loadTime');

        // 启动
        App::run();
    }

    /**
     * 检查缓存目录(Runtime)
     * 如果不存在则自动创建
     *
     * @return bealoon
     */
    private static function check_runtime() {

        // 如果不存在Runtime则创建
        if(!is_dir(RUNTIME_PATH)) {
            mkdir(RUNTIME_PATH);
        }
        // 如果Runtime不可写返回
        else if(!is_writeable(RUNTIME_PATH)) {
            exit(RUNTIME_PATH . 'is no writeable');
        }

        // 检查并创建Runtime下的缓存目录
        foreach (array(CACHE_PATH, LOG_PATH, TEMP_PATH, DATA_PATH) as $key => $value) {
            if(!is_dir($value)) mkdir($value);
        }

        return true;
    }

    /**
     * 注册错误和异常
     *
     * @return void
     */
    private static function register_error()
    {
        // register_shutdown_function(array('App','fatalError'));
        // set_error_handler(array('App','appError'));
        // set_exception_handler(array('App','appException'));
    }

}

// 启动
Smartthink::run();