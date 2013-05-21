<?php

use Think\Config as Config;
use Think\Log as Log;
use Think\Lang as Lang;
use Think\Debug as Debug;
use Think\Model as Model;
use Think\Import as Import;
use Think\Url as Url;
use Think\Cache as Cache;

// 最后留着的方法, A, C, D, L, M, R, S, U

/**
 * A函数用于实例化Action
 * 格式: [分组/]模块
 *
 * @param string $name Action资源地址
 * @param string $layer 控制层名称
 *
 * @return Action|false
 */
function A($name, $layer) {
    Debug::throw_exception('废弃了A方法');
}

/**
 * 重写C方法，改为引用Config方法
 *
 * @param string $name
 */
function C($name=null, $value=null) {

    // 无参数时获取所有
    if (empty($name)) {
        return Config::getAll();
    }

    // 字符串，为空则获取
    if(is_string($name) && is_null($value)) {
        return Config::get($name);
    }

    // 字符串，不为空则单个设置
    if(is_string($name) && !is_null($value)) {
        return Config::set($name, $value);
    }

    // 数组设置
    if(is_array($name)) {
        return Config::setAll($name);
    }
    
    // 避免非法参数
    Debug::throw_exception('C funtion error!');
}

/**
 * D函数用于实例化Model
 * 格式: 分组/模块
 *
 * @param string $name Model资源地址
 * @param string $layer 模型层名称
 *
 * @return Model
 */
function D($name = null, $groupName = null)
{
    // 空值，返回实力化后的Model类
    if(is_null($name)) {
        return new Model;
    }

    // 缓存
    static $_model = array();

    // 分组名默认值
    if(is_null($groupName)) {
        $groupName = GROUP_NAME;
    }

    // 获得类名
    $class = "App\\" . $groupName . "\\Model\\" . $name . "Model";

    // 缓存存在则返回
    if(isset($_model[$class])) {
        return $_model[$class];
    }

    // 检查类是否存在，如果不存在则实例化Model类
    if(class_exists($class)) {
        $model = new $class($name);
    }
    else {
        $model = new Model($name);
    }

    // 存入缓存
    return $_model[$class] = $model;
}

/**
 * 获取和设置语言定义(不区分大小写)
 *
 * @param string|array $name 语言变量
 * @param string $value 语言值
 *
 * @return mixed
 */
function L($name = null, $value = null) {

    // 空参数返回所有定义
    if (empty($name))
        return Lang::getAll();

    // 获取
    if(is_string($name) && is_null($value)) {
        return Lang::get($name);
    }

    // 设置
    if(is_string($name) && !is_null($value)) {
        return Lang::set($name, $value);
    }

    // 批量定义
    if(is_array($name)) {
        return Lang::setAll($name);
    }
}

/**
 * M函数用于实例化一个没有模型文件的Model
 *
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 *
 * @return Model
 */
function M($name = '', $tablePrefix = '', $connection = '') {

    // 缓存
    static $_model  = array();

    // 解析基础模型
    if(strpos($name, ':')) {
        list($class, $name) = explode(':', $name);
    }
    else {
        $class = 'Model';
    }

    // 表名
    $guid = $tablePrefix . $name . '_' . $class;

    if (!isset($_model[$guid])) {
        $class = 'Think\\' . $class;
        $_model[$guid] = new $class($name, $tablePrefix, $connection);
    }

    return $_model[$guid];
}

/**
 * 远程调用模块的操作方法 URL 参数格式 [项目://][分组/]模块/操作
 *
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组 
 * @param string $layer 要调用的控制层名称
 *
 * @return mixed
 */
function R($url, $vars=array(), $layer = '') {

    // 分析路径
    $info   =   pathinfo($url);
    $action =   $info['basename'];
    $module =   $info['dirname'];

    // 载入Controller
    $class  = Import::controller(GROUP_NAME, $module);

    // 判断是否存在并执行
    if($class){
        if(is_string($vars)) {
            parse_str($vars,$vars);
        }
        return call_user_func_array(array(&$class,$action.C('ACTION_SUFFIX')),$vars);
    }else{
        return false;
    }
}

/**
 * 缓存管理
 *
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 *
 * @return mixed
 */
function S($name, $value = '', $options = null) {

    static $cache = '';

    // 缓存操作的同时初始化
    if(is_array($options)) {

        $type = isset($options['type']) ? $options['type'] : '';
        $cache = Cache::getInstance($type, $options);
    }

    // 缓存初始化
    else if(is_array($name)) {

        $type = isset($name['type']) ? $name['type'] : '';
        $cache = Cache::getInstance($type, $name);
        return $cache;
    }

    // 自动初始化
    else if(empty($cache)) {

        $cache = Cache::getInstance();
    }

    // 获取缓存
    if($value === '') {

        return $cache->get($name);
    }

    // 删除缓存
    else if(is_null($value)) {

        return $cache->rm($name);
    }

    // 缓存数据
    else {
        
        $expire = is_numeric($options) ? $options : NULL;
        return $cache->set($name, $value, $expire);
    }
}

/**
 * URL组装 支持不同URL模式
 *
 * @param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $redirect 是否跳转，如果设置为true则表示跳转到该URL地址
 * @param boolean $domain 是否显示域名
 *
 * @return string
 */
function U($url = '', $vars = '', $suffix = true, $domain = false) {

    return Url::make($url, $vars, $suffix, $domain);
}

/**
 * 去除代码中的空白和注释
 *
 * @param string $content 代码内容
 *
 * @return string
 */
function strip_whitespace($content) {
    $stripStr   = '';
    //分析php源码
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr  .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr  .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr  .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 *
 * @param string $name 字符串
 * @param integer $type 转换类型
 *
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
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
function dump($var, $echo=true, $label=null, $strict=true) {
    return Debug::dump($var, $echo, $label, $strict);
}

/**
 * 过滤器方法 引用传值
 *
 * @param string $name 过滤器名称
 * @param string $content 要过滤的内容
 *
 * @return void
 */
function filter($name, &$content) {
    $class      =   $name . 'Filter';
    Import::load(GROUP_PATH . 'Filter/' . $class . EXT);
    $filter     =   new $class();
    $content    =   $filter->run($content);
}

/**
 * 取得对象实例 支持调用类的静态方法
 *
 * @param string $name 类名
 * @param string $method 方法名，如果为空则返回实例化对象
 * @param array $args 调用参数
 *
 * @return object
 */
function get_instance_of($name, $method='', $args=array()) {
    static $_instance = array();
    $identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);
    if (!isset($_instance[$identify])) {
        if (class_exists($name)) {
            $o = new $name();
            if (method_exists($o, $method)) {
                if (!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                } else {
                    $_instance[$identify] = $o->$method();
                }
            }
            else
                $_instance[$identify] = $o;
        }
        else
            Debug::halt(L('_CLASS_NOT_EXIST_') . ':' . $name);
    }
    return $_instance[$identify];
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 *
 * @param mixed $mix 变量
 *
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 过滤表单中的表达式
 *
 * @param string &$value
 *
 * @return void
 */
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}