<?php
namespace Think;
/**
 * Core/App.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Core.App
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use Think\Session as Session;
use Think\Exception as Exception;
use \ReflectionException;
use \ReflectionMethod;
use \ReflectionClass;

/**
 * App Class
 * 项目加载，配置初始化，错误处理
 */
class App {

    /**
     * 注册异常、autoload
     * 解析路由、加载配置
     * 启动session
     * 运行控制器
     *
     * @return void
     */
    static public function run() {

        // -------------------------------------------
        // 解析分配器，找到分组设置
        // -------------------------------------------
        Dispatch::init();

        // -------------------------------------------
        // 加载配置
        // -------------------------------------------
        static::load_config();

        // -------------------------------------------
        // 加载行为
        // -------------------------------------------
        static::load_tag();

        // -------------------------------------------
        // 加载语言包
        // -------------------------------------------
        static::load_lang();

        // -------------------------------------------
        // 分配分组内路由细节
        // -------------------------------------------
        static::load_route();

        // -------------------------------------------
        // 项目初始化标签
        // -------------------------------------------
        Tag::listen('app_init');

        // -------------------------------------------
        // 初始化
        // -------------------------------------------
        static::init();

        // -------------------------------------------
        // 项目开始标签
        // -------------------------------------------
        Tag::listen('app_begin');

        // -------------------------------------------
        // Session初始化
        // -------------------------------------------
        Session::config(Config::get('SESSION_OPTIONS'));

        // -------------------------------------------
        // 记录应用初始化时间
        // -------------------------------------------
        Debug::mark('initTime');

        // -------------------------------------------
        // 项目执行前检查访问者权限
        // -------------------------------------------
        Tag::listen('app_auth');

        // -------------------------------------------
        // 执行程序
        // -------------------------------------------
        static::exec();

        // -------------------------------------------
        // 项目结束标签
        // -------------------------------------------
        Tag::listen('app_end');

        // -------------------------------------------
        // 保存日志记录
        // -------------------------------------------
        if(Config::get('LOG_RECORD')) {
            // Log::save();
            // Log::info('------------------- |' . CONTROLLER_NAME . ' & ' . ACTION_NAME);
        }
    }

    /**
     * 载入配置
     *
     * @return void
     */
    private static function load_config()
    {
        foreach (array(FRAME_PATH, APP_PATH, GROUP_PATH) as $key => $path) {
            static::parseConfig($path);
        }

        static::loadExtConfig();
    }

    /**
     * 解析配置
     *
     * @return void
     */
    private static function parseConfig($path)
    {
        // 通用
        if(is_file($path . 'Conf/config.php')) {
            Config::set(include $path . 'Conf/config.php');
        }

        // 调试
        if(APP_DEBUG) {
            if(is_file($path . 'Conf/debug.php')) {
                Config::set(include $path . 'Conf/debug.php');
            }
        }
    }

    /**
     * 加载扩展配置文件
     *
     * @return void
     */
    private static function loadExtConfig()
    {
        // 加载自定义的动态配置文件
        if(Config::get('LOAD_EXT_CONFIG'))
        {
            $configs = Config::get('LOAD_EXT_CONFIG');

            // 字符串解析数组
            if(is_string($configs)) {
                $configs =  explode(',',$configs);
            }

            // 遍历加载
            foreach ($configs as $key => $config)
            {
                $file = CONF_PATH . $config . '.php';
                if(is_file($file)) {
                    is_numeric($key) ? Config::set(include $file) : Config::set($key,include $file);
                }
            }
        }
    }

    /**
     * 加载行为
     *
     * @return void
     */
    private static function load_tag()
    {
        // 核心行为
        Config::set('extends', include FRAME_PATH . 'Conf/tags.php');

        // 项目行为
        if(is_file(CONF_PATH . 'tags.php')) {
            Config::set('tags', include CONF_PATH . 'tags.php');
        }

        // 分组行为
        if(is_file(GROUP_PATH . 'Conf/tags.php')) {
            Config::set('tags', include GROUP_PATH . 'Conf/tags.php');
        }
    }

    /**
     * 加载语言包
     *
     * @return void
     */
    private static function load_lang()
    {
        Lang::set(include FRAME_PATH . 'Lang/' . strtolower(Config::get('DEFAULT_LANG')) . '.php');

        // 项目行为
        if(is_file(APP_PATH . 'Lang/' . strtolower(Config::get('DEFAULT_LANG')) . '.php')) {
            Lang::set(include APP_PATH . 'Lang/' . strtolower(Config::get('DEFAULT_LANG')) . '.php');
        }

        // 分组行为
        if(is_file(GROUP_PATH . 'Lang/' . strtolower(Config::get('DEFAULT_LANG')) . '.php')) {
            Lang::set(include GROUP_PATH . 'Lang/' . strtolower(Config::get('DEFAULT_LANG')) . '.php');
        }
    }

    private static function load_route()
    {
        $class = Config::get('GROUP_ROUTE_CLASS');
        
        if(class_exists($class)) {
            $class::init();
        }
        else {
            Debug::output(new Exception("不存在被定义的Route文件,\"" . $class . "\""));
        }
    }

    public static function exec()
    {   
        $groupName = GROUP_NAME;
        $controllerName = CONTROLLER_NAME;
        $actionName = ACTION_NAME;

        // 安全过滤
        try {
            if(!preg_match('/^[A-Za-z](\w)*$/', $controllerName)) {
                throw new Exception("控制器名不符合规范");
            }
            if(!preg_match('/^[A-Za-z](\w)*$/', $actionName)) {
                throw new Exception("方法名不符合规范");
            }
        }
        catch(Exception $error) {
            Debug::output($error);
        }

        // 是否存在控制器
        try {
            $ControllerClass = static::getControllerClass($groupName, $controllerName);

            if(!class_exists($ControllerClass)) {
                throw new Exception("不存在该控制器");
            }

            $Controller = new $ControllerClass;
        }
        catch(Exception $error) {
            Debug::output($error);
        }

        // 执行控制器方法
        try {

            $method = 'action_' . $actionName;

            if(method_exists($Controller, $method)) {
                $Controller->$method();
            }
            else {

                $current = strtolower($_SERVER['REQUEST_METHOD']);
                $method = array('get', 'post', 'put', 'delete', 'head');

                if(in_array($current, $method)) {

                    // method和操作名组成的类
                    $current = $current . '_' . $actionName;

                    // 该类是否存在该方法
                    if(method_exists($Controller, $current)) {
                        $Controller->$current();
                    }
                    // 不存在输出
                    else {
                        throw new Exception("不存在指定的控制器方法");
                    }
                }
                // 方法错误输出
                else {
                    throw new Exception("不存在'$method'方法");
                }
            }
        }
        catch(Exception $error) {
            Debug::output($error);
        }
    }

    public static function getControllerClass($groupName, $controllerName)
    {
        return "App\\" . $groupName . "\\Controller\\" . $controllerName . "Controller";
    }

    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    static public function init()
    {

        // 设置系统时区
        date_default_timezone_set(Config::get('DEFAULT_TIMEZONE'));

        // 定义当前请求的系统常量
        define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
        define('IS_GET',        REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST',       REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT',        REQUEST_METHOD == 'PUT' ? true : false);
        define('IS_DELETE',     REQUEST_METHOD == 'DELETE' ? true : false);
        define('IS_AJAX',       ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[Config::get('VAR_AJAX_SUBMIT')]) || !empty($_GET[Config::get('VAR_AJAX_SUBMIT')])) ? true : false);

        // URL调度结束标签
        Tag::listen('url_dispatch');

        // TODO: 删除页面压缩功能，改为使用Smarty进行页面压缩管理
        // 页面压缩输出支持
        // if(Config::get('OUTPUT_ENCODE')) {
        //     $zlib = ini_get('zlib.output_compression');
        //     if(empty($zlib)) {
        //         ob_start('ob_gzhandler');
        //     }
        // }

        // 系统变量安全过滤
        if(Config::get('VAR_FILTERS')) {
            $filters = explode(',',Config::get('VAR_FILTERS'));
            foreach($filters as $filter){
                // 全局参数过滤
                array_walk_recursive($_POST,$filter);
                array_walk_recursive($_GET,$filter);
            }
        }

        // 配置主题目录
        define('THEME_PATH', GROUP_PATH . 'Tpl/');

        // 缓存路径
        Config::set('CACHE_PATH', CACHE_PATH . GROUP_NAME . '/');

        // 动态配置 TMPL_EXCEPTION_FILE，改为绝对地址
        Config::set('TMPL_EXCEPTION_FILE', realpath(Config::get('TMPL_EXCEPTION_FILE')));
    }
}