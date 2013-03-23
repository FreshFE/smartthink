<?php

// PHP版本
if(version_compare(PHP_VERSION,'5.3.0','<')) {
    die('require PHP > 5.3.0');
}

/**
 * 框架版本
 *
 * @const THINK_VERSION
 */
define('THINK_VERSION', '4.0.0 Beta');

/**
 * 设置PHP的MAGIC_QUOTES_GPC
 *
 * @const MAGIC_QUOTES_GPC
 */
if(version_compare(PHP_VERSION, '5.4.0', '<')) {
    ini_set('magic_quotes_runtime', 0);
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
}
else {
    define('MAGIC_QUOTES_GPC', false);
}

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
defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));

/**
 * 在非命令行执行中定义常量
 *
 * @const _PHP_FILE_
 * @const __ROOT__
 *
 * @const URL_COMMON
 * @const URL_PATHINFO
 * @const URL_REWRITE
 * @const URL_COMPAT
 */
if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            // CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        // 网站URL根目录
        if( strtoupper(APP_NAME) == strtoupper(basename(dirname(_PHP_FILE_))) ) {
            $_root = dirname(dirname(_PHP_FILE_));
        }else {
            $_root = dirname(_PHP_FILE_);
        }
        define('__ROOT__',   (($_root=='/' || $_root=='\\')?'':$_root));
    }

    //支持的URL模式
    define('URL_COMMON',      0);   //普通模式
    define('URL_PATHINFO',    1);   //PATHINFO模式
    define('URL_REWRITE',     2);   //REWRITE模式
    define('URL_COMPAT',      3);   // 兼容模式
}

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
 */
defined('CORE_PATH')    or define('CORE_PATH',      THINK_PATH.'Lib/'); // 系统核心类库目录

defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.'Common/'); // 项目公共目录
defined('LIB_PATH')     or define('LIB_PATH',       APP_PATH.'Lib/'); // 项目类库目录
defined('CONF_PATH')    or define('CONF_PATH',      APP_PATH.'Conf/'); // 项目配置目录
defined('LANG_PATH')    or define('LANG_PATH',      APP_PATH.'Lang/'); // 项目语言包目录
defined('TMPL_PATH')    or define('TMPL_PATH',      APP_PATH.'Tpl/'); // 项目模板目录
defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.'Html/'); // 项目静态目录

defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'Logs/'); // 项目日志目录
defined('TEMP_PATH')    or define('TEMP_PATH',      RUNTIME_PATH.'Temp/'); // 项目缓存目录
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'Data/'); // 项目数据目录
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'Cache/'); // 项目模板缓存目录

/**
 * 为了方便导入第三方类库 设置Vendor目录到include_path
 */
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);

/**
 * 加载运行时所需要的文件
 * 检查调试模式创建目录
 */
function load_runtime_file() {

    // TODO: 重新编辑该文件，对象化
    // 加载系统基础函数库
    require CORE_PATH.'Core/Import.class.php';

    // 载入核心，异常和行为
    Import::load(THINK_PATH.'Common/common.php');
    Import::load(CORE_PATH.'Core/Think.class.php');
    Import::load(CORE_PATH.'Core/ThinkException.class.php');
    Import::load(CORE_PATH.'Core/Behavior.class.php');

    // TODO: 分析删除该代码
    // 加载系统类库别名定义
    Import::alias_import(include THINK_PATH.'Conf/alias.php');

    // 调试模式下检查路径和文件
    if(APP_DEBUG){
        // 创建项目目录结构
        if(!is_dir(LIB_PATH)) {
            throw_exception('不存在项目目录结构');
        }

        // 检查缓存目录
        if(!is_dir(CACHE_PATH)) {
            check_runtime();
        }

        // 调试模式切换删除编译缓存
        if(is_file(RUNTIME_FILE)) {
            unlink(RUNTIME_FILE);
        }
    }
}

/**
 * 检查缓存目录(Runtime)
 * 如果不存在则自动创建
 *
 * @return bealoon
 */
function check_runtime() {

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
 * 加载执行Runtime启动项目
 */
load_runtime_file();

// 记录加载文件时间
G('loadTime');

/**
 * 初始化项目
 */
Think::Start();