<?php
/**
 * Core/Think.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Core.Think
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * Think Class
 * 项目加载，配置初始化，错误处理
 */
class Think {

	/**
	 * 实例缓存
	 *
	 * var array
	 */
    private static $_instance = array();

    /**
     * 应用程序初始化
     *
     * @return void
     */
    public static function start() {

        // 设定错误和异常处理
        register_shutdown_function(array('Think','fatalError'));
        set_error_handler(array('Think','appError'));
        set_exception_handler(array('Think','appException'));

        // 注册AUTOLOAD方法
        spl_autoload_register(array('Think', 'autoload'));
        
        // 预编译项目
        Think::buildApp();

        // 运行应用
        App::run();
    }

    /**
     * 读取配置信息 编译项目
     *
     * @return string
     */
    private static function buildApp() {

        // 核心配置文件
        Config::set(include THINK_PATH . 'Conf/convention.php');

        // 项目配置文件
        if(is_file(CONF_PATH . 'config.php')) {
            Config::set(include CONF_PATH . 'config.php');
        }

        // 核心行为配置
        if(Config::get('APP_TAGS_ON')) {
            Config::set('extends', include THINK_PATH . 'Conf/tags.php');
        }

        // 项目行为配置
        if(is_file(CONF_PATH . 'tags.php')) {
            Config::set('tags', include CONF_PATH . 'tags.php');
        }

        // 调试模式下加载调试文件
        if(APP_DEBUG) {

            // 调试模式加载系统默认的配置文件
            C(include THINK_PATH . 'Conf/debug.php');

            if(is_file(CONF_PATH . 'debug.php')) {
                Config::set(include CONF_PATH . 'debug.php');
            }
        }

        // 核心语言包
        Lang::set(include THINK_PATH . 'Lang/' . strtolower(C('DEFAULT_LANG')) . '.php');

        // 加载动态项目公共文件和配置
        static::load_ext_file();

        // URL调度
        Router::dispatch();

        // 加载分组配置文件
        if(is_file(GROUP_PATH . 'Conf/config.php')) {
            C(include GROUP_PATH . 'Conf/config.php');
        }

        // 加载分组tags文件定义
        if(is_file(GROUP_PATH . 'Conf/tags.php')) {
            C('tags', include GROUP_PATH . 'Conf/tags.php');
        }
    }

    /**
     * 加载扩展配置文件
     *
     * @return void
     */
    private static function load_ext_file() {

        // 加载自定义的动态配置文件
        if(C('LOAD_EXT_CONFIG')) {

            $configs = C('LOAD_EXT_CONFIG');

            if(is_string($configs)) {
                $configs =  explode(',',$configs);
            }

            foreach ($configs as $key => $config) {

                $file = CONF_PATH . $config . '.php';

                if(is_file($file)) {
                    is_numeric($key) ? C(include $file) : C($key,include $file);
                }
            }
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
    public static function autoload($class) {

        // 定义基本文件
        $file = $class . '.class.php';

        // 加载行为
        if(substr($class, -8) == 'Behavior') {
            $files = array(
                CORE_PATH . 'Behavior/' . $file,
                LIB_PATH . 'Behavior/' . $file
            );
        }

        // 加载控制器
        else if(substr($class, -10) == 'Controller') {
            $files = array(
                LIB_PATH . 'Controller/' . GROUP_NAME . $file,
                LIB_PATH . 'Controller/' . $file
            );
        }

        // 加载模型
        else if(substr($class, -5) == 'Model') {
            $files = array(
                LIB_PATH . 'Model/' . GROUP_NAME . $file,
                LIB_PATH . 'Model/' . $file
            );
        }

        // 加载缓存驱动
        else if(substr($class, 0, 5) == 'Cache') {
            $files = array(
                CORE_PATH . 'Driver/Cache/' . $file
            );
        }

        // 加载数据库驱动
        else if(substr($class, 0, 2) == 'Db') {
            $files = array(
                CORE_PATH . 'Driver/Db/' . $file
            );
        }

        // 载入
        if($files) {
            Import::loads($files);    
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     *
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     *
     * @return object
     */
    public static function instance($class, $method = '') {

		$identify = $class . $method;

		if(!isset(self::$_instance[$identify])) {

			if(class_exists($class)) {

				$o = new $class();

				if(!empty($method) && method_exists($o,$method)) {
					self::$_instance[$identify] = call_user_func_array(array(&$o, $method));
				}					
				else {
					self::$_instance[$identify] = $o;
				}
			}
			else {
				Debug::halt(L('_CLASS_NOT_EXIST_') . ':' . $class);
			}
		}

		return self::$_instance[$identify];
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
    public static function appError($errno, $errstr, $errfile, $errline) {

		switch ($errno) {
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:

				ob_end_clean();

				// 页面压缩输出支持
				if(C('OUTPUT_ENCODE')) {
					$zlib = ini_get('zlib.output_compression');
					if(empty($zlib)) ob_start('ob_gzhandler');
				}

				$errorStr = "$errstr " . $errfile . " 第 $errline 行.";

				if(C('LOG_RECORD')) {
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
	public static function fatalError() {
		if ($e = error_get_last()) {
			Think::appError($e['type'], $e['message'], $e['file'], $e['line']);
		}
	}

}