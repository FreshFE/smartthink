<?php
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
        // 注册错误和异常
        // -------------------------------------------
        static::registerError();

        // -------------------------------------------
        // 注册autoload方法
        // -------------------------------------------
        static::registerAutoload();

        // -------------------------------------------
        // 解析分组
        // -------------------------------------------
        static::parseGroupPath();

        // -------------------------------------------
        // 解析路由
        // -------------------------------------------
        static::loadRoutes();

        // -------------------------------------------
        // 加载配置
        // -------------------------------------------
        static::loadConfig();

        // -------------------------------------------
        // 加载行为
        // -------------------------------------------
        static::loadTag();

        // -------------------------------------------
        // 加载语言包
        // -------------------------------------------
        static::loadLang();

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
            Log::save();
        }
    }

    /**
     * 注册自动加载
     *
     * @return void
     */
    private static function registerAutoload()
    {
        spl_autoload_register(array('App', 'autoload'));
    }

    /**
     * 注册错误和异常
     *
     * @return void
     */
    private static function registerError()
    {
        register_shutdown_function(array('App','fatalError'));
        set_error_handler(array('App','appError'));
        set_exception_handler(array('App','appException'));
    }

    /**
     * 解析分组
     */
    private static function parseGroupPath()
    {

    }

    /**
     * 解析路由
     *
     * TODO: 分离路由，查找前缀目录
     */
    private static function loadRoutes()
    {
        Router::dispatch();
    }

    /**
     * 载入配置
     *
     * @return void
     */
    private static function loadConfig()
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
    private static function loadTag()
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
    private static function loadLang()
    {
        Lang::set(include FRAME_PATH . 'Lang/' . strtolower(Config::get('DEFAULT_LANG')) . '.php');
    }

    /**
     * 执行controller->action
     *
     * @return void
     */
    static public function exec()
    {
        // Controller name 安全过滤
        if(!preg_match('/^[A-Za-z](\w)*$/', CONTROLLER_NAME))
        {
            $module = false;
        }
        // 通过A方法实例化Controller
        else {
            $module = Import::controller(CONTROLLER_NAME);
        }

        // 执行空控制器
        if(!$module)
        {
            $module = Import::controller('Empty');
            if(!$module){
                Http::_404(L('_MODULE_NOT_EXIST_') . ':' . CONTROLLER_NAME);
            }
        }

        // 获取控制器操作名
        $action = ACTION_NAME;

        // 定义模板名称
        Config::set('TEMPLATE_NAME', THEME_PATH . CONTROLLER_NAME . '/' . $action . '.html');

        try
        {
            // Action name 安全过滤
            if(!preg_match('/^[A-Za-z](\w)*$/', $action)) {
                throw new ReflectionException();
            }

            // 对当前控制器的方法执行操作映射
            $method = new ReflectionMethod($module, $action);
            
            // public方法
            if($method->isPublic())
            {
                // 映射执行
                $class = new ReflectionClass($module);

                // 前置操作
                if($class->hasMethod('_before_' . $action))
                {
                    $before = $class->getMethod('_before_' . $action);

                    // public并执行
                    if($before->isPublic()) {
                        $before->invoke($module);
                    }
                }

                // URL参数绑定检测
                if(Config::get('URL_PARAMS_BIND') && $method->getNumberOfParameters() > 0)
                {
                    switch ($_SERVER['REQUEST_METHOD'])
                    {
                        case 'POST':
                            $vars = $_POST;
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $vars);
                            break;
                        default:
                            $vars = $_GET;
                    }

                    $params = $method->getParameters();

                    foreach ($params as $param)
                    {
                        $name = $param->getName();

                        if(isset($vars[$name]))
                        {
                            $args[] =  $vars[$name];
                        }
                        elseif($param->isDefaultValueAvailable())
                        {
                            $args[] = $param->getDefaultValue();
                        }
                        else {
                            Debug::throw_exception(L('_PARAM_ERROR_').':'.$name);
                        }
                    }

                    $method->invokeArgs($module,$args);
                }
                else {
                    $method->invoke($module);
                }

                // 后置操作
                if($class->hasMethod('_after_' . $action)) {

                    $after = $class->getMethod('_after_' . $action);

                    // public并执行
                    if($after->isPublic()) {
                        $after->invoke($module);
                    }
                }
            }
            // 操作方法不是Public 抛出异常
            else {
                throw new ReflectionException();
            }
        }
        catch (ReflectionException $e)
        { 
            // 方法调用发生异常后 引导到__call方法处理
            $method = new ReflectionMethod($module,'__call');
            $method->invokeArgs($module,array($action,''));
        }
    }

    /**
     * 系统自动加载ThinkPHP类库
     * 并且支持配置自动加载路径
     *
     * @param string $class 对象类名
     *
     * @return void
     */
    public static function autoload($classname) {

        $filename = str_replace('\\', '/', $classname);
        $filename = str_replace('meSmart/', CORE_PATH, $filename);
        $filename = str_replace('App/', APP_PATH, $filename);

        Import::load($filename.EXT);

        // echo $filename . '<br>';
        // echo '.......-----........<br>';
        // Debug::throw_exception('ss');
        // exit();
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     *
     * @param string $classname 对象类名
     * @param string $method 类的静态方法名
     *
     * @return object
     */
    public static function instance($classname, $method = '') {

        // 静态缓存
        static $_instance = array();

        // 标识
        $identify = $classname . $method;

        // 不存在缓存
        if(!isset($_instance[$identify]))
        {
            // 存在该类名
            if(class_exists($classname))
            {
                $obj = new $classname();
                if(!empty($method) && method_exists($obj,$method))
                {
                    $_instance[$identify] = call_user_func_array(array(&$obj, $method));
                }                   
                else {
                    $_instance[$identify] = $obj;
                }
            }
            else {
                Debug::halt(L('_CLASS_NOT_EXIST_') . ':' . $classname);
            }
        }
        // 返回缓存
        return $_instance[$identify];
    }

    /**
     * 自定义异常处理
     *
     * @param mixed $e 异常对象
     *
     * @return void
     */
    public static function appException($e) {
        Debug::halt($e->__toString());
    }

    /**
     * 自定义错误处理
     *
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     *
     * @return void
     */
    public static function appError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:

                ob_end_clean();

                // 页面压缩输出支持
                if(Config::get('OUTPUT_ENCODE')) {
                    $zlib = ini_get('zlib.output_compression');
                    if(empty($zlib)) ob_start('ob_gzhandler');
                }

                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";

                if(Config::get('LOG_RECORD')) {
                    Log::write("[$errno] " . $errorStr, Log::ERR);
                }

                function_exists('halt') ? Debug::halt($errorStr) : exit('ERROR:' . $errorStr);
                break;

            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:

                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                Debug::trace($errorStr, '', 'NOTIC');
                break;
        }
    }
    
    /**
     * 致命错误捕获
     *
     * @return void
     */
    public static function fatalError()
    {
        if ($e = error_get_last()) {
            App::appError($e['type'], $e['message'], $e['file'], $e['line']);
        }
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

        // 页面压缩输出支持
        if(Config::get('OUTPUT_ENCODE')) {
            $zlib = ini_get('zlib.output_compression');
            if(empty($zlib)) {
                ob_start('ob_gzhandler');
            }
        }

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
        define('THEME_PATH', TMPL_PATH . GROUP_NAME . '/');
        define('APP_TMPL_PATH' , __ROOT__ . '/' . APP_NAME . (APP_NAME ? '/' : '') . basename(TMPL_PATH) . '/' . GROUP_NAME . '/');

        // 缓存路径
        Config::set('CACHE_PATH', CACHE_PATH . GROUP_NAME . '/');

        // 动态配置 TMPL_EXCEPTION_FILE，改为绝对地址
        Config::set('TMPL_EXCEPTION_FILE', realpath(Config::get('TMPL_EXCEPTION_FILE')));
    }
}