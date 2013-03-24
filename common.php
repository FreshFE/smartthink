<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * Think 基础函数库
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 */

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
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
 * A函数用于实例化Action 格式：[项目://][分组/]模块
 * @param string $name Action资源地址
 * @param string $layer 控制层名称
 * @param boolean $common 是否公共目录
 * @return Action|false
 */
function A($name, $layer='') {

    // 缓存
    static $_action = array();

    // 默认控制器名称
    $layer = $layer?$layer:C('DEFAULT_C_LAYER');

    // 指定项目
    if(strpos($name,'://')) {
        $name = str_replace('://','/'.$layer.'/',$name);
    }
    // 本地项目
    else{
        $name = '@/'.$layer.'/'.$name;
    }

    // 静态缓存内是否存在
    if(isset($_action[$name])) {
        return $_action[$name];
    }

    // 解析
    $path = explode('/',$name);
    
    // 独立分组
    if(count($path)>3 && 1 == C('APP_GROUP_MODE')) {
        $baseUrl = $path[0] == '@' ? dirname(BASE_LIB_PATH) : APP_PATH . '../' . $path[0] . '/' . C('APP_GROUP_PATH') . '/';
        Import::uses($path[2] . '/' . $path[1] . '/' . $path[3] . $layer, $baseUrl);
    }
    // 加载LIB_PATH
    else{
        Import::uses(str_replace('@/','',$name).$layer, LIB_PATH);
    }

    // 生成class名称
    $class = basename($name.$layer);

    // 检测导入的文件是否存在该类
    // 存在则写入缓存
    if(class_exists($class,false)) {
        $action = new $class();
        $_action[$name] = $action;
        return $action;
    }
    // 不存在则返回
    else {
        return false;
    }
}

/**
 * 执行某个行为
 * @param string $name 行为名称
 * @param Mixed $params 传人的参数
 * @return void
 */
function B($name, &$params=NULL) {
    $class      = $name.'Behavior';
    if(APP_DEBUG) {
        G('behaviorStart');
    }
    $behavior   = new $class();
    $behavior->run($params);
    if(APP_DEBUG) { // 记录行为的执行日志
        G('behaviorEnd');
        trace('Run '.$name.' Behavior [ RunTime:'.G('behaviorStart','behaviorEnd',6).'s ]','','INFO');
    }
}

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed
 */
function C($name=null, $value=null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        if(!empty($value) && $array = S('c_'.$value)) {
            $_config = array_merge($_config, array_change_key_case($array));
        }
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name));
        if(!empty($value)) {// 保存配置值
            S('c_'.$value,$_config);
        }
        return;
    }
    return null; // 避免非法参数
}

/**
 * D函数用于实例化Model 格式 项目://分组/模块
 * @param string $name Model资源地址
 * @param string $layer 业务层名称
 * @return Model
 */
function D($name='',$layer='') {
    if(empty($name)) return new Model;
    static $_model  =   array();
    $layer          =   $layer?$layer:C('DEFAULT_M_LAYER');
    if(strpos($name,'://')) {// 指定项目
        $name       =   str_replace('://','/'.$layer.'/',$name);
    }else{
        $name       =   C('DEFAULT_APP').'/'.$layer.'/'.$name;
    }
    if(isset($_model[$name]))   return $_model[$name];
    $path           =   explode('/',$name);
    // dump(str_replace('@', '', $name).$layer);
    if(count($path)>3 && 1 == C('APP_GROUP_MODE')) { // 独立分组
        $baseUrl    =   $path[0]== '@' ? dirname(BASE_LIB_PATH) : APP_PATH.'../'.$path[0].'/'.C('APP_GROUP_PATH').'/';
        Import::uses($path[2].'/'.$path[1].'/'.$path[3].$layer,$baseUrl);
    }else{
        Import::uses(str_replace('@', '', $name).$layer, LIB_PATH);
    } 
    $class          =   basename($name.$layer);
    if(class_exists($class)) {
        $model      =   new $class(basename($name));
    }else {
        $model      =   new Model(basename($name));
    }
    $_model[$name]  =  $model;
    return $model;
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value='', $path=DATA_PATH) {
    static $_cache  = array();
    $filename       = $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            return false !== strpos($name,'*')?array_map("unlink", glob($filename)):unlink($filename);
        } else {
            // 缓存数据
            $dir            =   dirname($filename);
            // 目录不存在则创建
            if (!is_dir($dir))
                mkdir($dir,0755,true);
            $_cache[$name]  =   $value;
            return file_put_contents($filename, strip_whitespace("<?php\treturn " . var_export($value, true) . ";?>"));
        }
    }
    if (isset($_cache[$name]))
        return $_cache[$name];
    // 获取缓存数据
    if (is_file($filename)) {
        $value          =   include $filename;
        $_cache[$name]  =   $value;
    } else {
        $value          =   false;
    }
    return $value;
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m 
 * @return mixed
 */
function G($start,$end='',$dec=4) {
    static $_info       =   array();
    static $_mem        =   array();
    if(is_float($end)) { // 记录时间
        $_info[$start]  =   $end;
    }elseif(!empty($end)){ // 统计时间和内存使用
        if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
        if(MEMORY_LIMIT_ON && $dec=='m'){
            if(!isset($_mem[$end])) $_mem[$end]     =  memory_get_usage();
            return number_format(($_mem[$end]-$_mem[$start])/1024);          
        }else{
            return number_format(($_info[$end]-$_info[$start]),$dec);
        }       
            
    }else{ // 记录时间和内存使用
        $_info[$start]  =  microtime(TRUE);
        if(MEMORY_LIMIT_ON) $_mem[$start]           =  memory_get_usage();
    }
}

/**
 * 获取和设置语言定义(不区分大小写)
 * @param string|array $name 语言变量
 * @param string $value 语言值
 * @return mixed
 */
function L($name = null, $value = null) {
    
    // 定义静态数组
    static $_lang = array();

    // 空参数返回所有定义
    if (empty($name))
        return $_lang;

    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name)) {

        $name = strtoupper($name);

        // 如果空的则读取
        if (is_null($value))
            return isset($_lang[$name]) ? $_lang[$name] : $name;

        // 定义语言
        $_lang[$name] = $value;

        return $value;
    }

    // 批量定义
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));

    return $name;
}

/**
 * M函数用于实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'Model';
    }
    $guid           =   $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    return $_model[$guid];
}

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code> 
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @return mixed
 */
function N($key, $step=0,$save=false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
    if(false !== $save){ // 保存结果
        S('N_'.$key,$_num[$key],$save);
    }
}

/**
 * 远程调用模块的操作方法 URL 参数格式 [项目://][分组/]模块/操作
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组 
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url,$vars=array(),$layer='') {
    $info   =   pathinfo($url);
    $action =   $info['basename'];
    $module =   $info['dirname'];
    $class  =   A($module,$layer);
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
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
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
 * @param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $redirect 是否跳转，如果设置为true则表示跳转到该URL地址
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url='',$vars='',$suffix=true,$redirect=false,$domain=false) {
    // 解析URL
    $info   =  parse_url($url);
    $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
    if(isset($info['fragment'])) { // 解析锚点
        $anchor =   $info['fragment'];
        if(false !== strpos($anchor,'?')) { // 解析参数
            list($anchor,$info['query']) = explode('?',$anchor,2);
        }        
        if(false !== strpos($anchor,'@')) { // 解析域名
            list($anchor,$host)    =   explode('@',$anchor, 2);
        }
    }elseif(false !== strpos($url,'@')) { // 解析域名
        list($url,$host)    =   explode('@',$info['path'], 2);
    }
    // 解析子域名
    if(isset($host)) {
        $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
    }elseif($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if(C('APP_SUB_DOMAIN_DEPLOY') ) { // 开启子域名部署
            $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
            // '子域名'=>array('项目[/分组]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                if(false === strpos($key,'*') && 0=== strpos($url,$rule[0])) {
                    $domain = $key.strstr($domain,'.'); // 生成对应子域名
                    $url    =  substr_replace($url,'',0,strlen($rule[0]));
                    break;
                }
            }
        }
    }

    // 解析参数
    if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }
    
    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    if($url) {
        if(0=== strpos($url,'/')) {// 定义路由
            $route      =   true;
            $url        =   substr($url,1);
            if('/' != $depr) {
                $url    =   str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // 安全替换
                $url    =   str_replace('/',$depr,$url);
            }
            // 解析分组、模块和操作
            $url        =   trim($url,$depr);
            $path       =   explode($depr,$url);
            $var        =   array();
            $var[C('VAR_ACTION')]       =   !empty($path)?array_pop($path):ACTION_NAME;
            $var[C('VAR_MODULE')]       =   !empty($path)?array_pop($path):MODULE_NAME;
            if($maps = C('URL_ACTION_MAP')) {
                if(isset($maps[strtolower($var[C('VAR_MODULE')])])) {
                    $maps    =   $maps[strtolower($var[C('VAR_MODULE')])];
                    if($action = array_search(strtolower($var[C('VAR_ACTION')]),$maps)){
                        $var[C('VAR_ACTION')] = $action;
                    }
                }
            }
            if($maps = C('URL_MODULE_MAP')) {
                if($module = array_search(strtolower($var[C('VAR_MODULE')]),$maps)){
                    $var[C('VAR_MODULE')] = $module;
                }
            }            
            if(C('URL_CASE_INSENSITIVE')) {
                $var[C('VAR_MODULE')]   =   parse_name($var[C('VAR_MODULE')]);
            }
            if(!C('APP_SUB_DOMAIN_DEPLOY') && C('APP_GROUP_LIST')) {
                if(!empty($path)) {
                    $group                  =   array_pop($path);
                    $var[C('VAR_GROUP')]    =   $group;
                }else{
                    if(GROUP_NAME != C('DEFAULT_GROUP')) {
                        $var[C('VAR_GROUP')]=   GROUP_NAME;
                    }
                }
                if(C('URL_CASE_INSENSITIVE') && isset($var[C('VAR_GROUP')])) {
                    $var[C('VAR_GROUP')]    =  strtolower($var[C('VAR_GROUP')]);
                }
            }
        }
    }

    if(C('URL_MODEL') == 0) { // 普通模式URL转换
        $url        =   __APP__.'?'.http_build_query(array_reverse($var));
        if(!empty($vars)) {
            $vars   =   urldecode(http_build_query($vars));
            $url   .=   '&'.$vars;
        }
    }else{ // PATHINFO模式或者兼容URL模式
        if(isset($route)) {
            $url    =   __APP__.'/'.rtrim($url,$depr);
        }else{
            $url    =   __APP__.'/'.implode($depr,array_reverse($var));
        }
        if(!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val){
                if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
            }                
        }
        if($suffix) {
            $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
            if($pos = strpos($suffix, '|')){
                $suffix = substr($suffix, 0, $pos);
            }
            if($suffix && '/' != substr($url,-1)){
                $url  .=  '.'.ltrim($suffix,'.');
            }
        }
    }
    if(isset($anchor)){
        $url  .= '#'.$anchor;
    }
    if($domain) {
        $url   =  (is_ssl()?'https://':'http://').$domain.$url;
    }
    if($redirect) // 直接跳转URL
        redirect($url);
    else
        return $url;
}

// TODO: 重构
/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传人的参数
 * @param boolean $return 是否返回内容 
 * @return void
 */
function W($name, $data=array(), $return=false) {
    $class      =   $name . 'Widget';
    Import::load(BASE_LIB_PATH . 'Widget/' . $class . '.class.php');
    if (!class_exists($class))
        throw_exception(L('_CLASS_NOT_EXIST_') . ':' . $class);
    $widget     =   Think::instance($class);
    $content    =   $widget->render($data);
    if ($return)
        return $content;
    else
        echo $content;
}

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
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
 * 添加和获取页面Trace记录
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别 
 * @param boolean $record 是否记录日志
 * @return void
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
    static $_trace =  array();
    if('[think]' === $value){ // 获取trace信息
        return $_trace;
    }else{
        $info   =   ($label?$label.':':'').print_r($value,true);
        if('ERR' == $level && C('TRACE_EXCEPTION')) {// 抛出异常
            throw_exception($info);
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
function halt($error) {
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
function throw_exception($msg, $type='ThinkException', $code=0) {
    if (class_exists($type, false))
        throw new $type($msg, $code, true);
    else
        halt($msg);        // 异常类型不存在则输出错误信息字串
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
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

/**
 * 404处理 
 * 调试模式会抛异常 
 * 部署模式下面传入url参数可以指定跳转页面，否则发送404信息
 * @param string $msg 提示信息
 * @param string $url 跳转URL地址
 * @return void
 */
function _404($msg='',$url='') {
    APP_DEBUG && throw_exception($msg);
    if($msg && C('LOG_EXCEPTION_RECORD')) Log::write($msg);
    if(empty($url) && C('URL_404_REDIRECT')) {
        $url    =   C('URL_404_REDIRECT');
    }
    if($url) {
        redirect($url);
    }else{
        send_http_status(404);
        exit;
    }
}





/**
 * 过滤器方法 引用传值
 * @param string $name 过滤器名称
 * @param string $content 要过滤的内容
 * @return void
 */
function filter($name, &$content) {
    $class      =   $name . 'Filter';
    Import::load(BASE_LIB_PATH . 'Filter/' . $class . '.class.php');
    $filter     =   new $class();
    $content    =   $filter->run($content);
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

/**
 * 取得对象实例 支持调用类的静态方法
 * @param string $name 类名
 * @param string $method 方法名，如果为空则返回实例化对象
 * @param array $args 调用参数
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
            halt(L('_CLASS_NOT_EXIST_') . ':' . $name);
    }
    return $_instance[$identify];
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
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
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
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
    $xml   .= data_to_xml($data, $item, $id);
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
function data_to_xml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key  = $item;
        }
        $xml    .=  "<{$key}{$attr}>";
        $xml    .=  (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml    .=  "</{$key}>";
    }
    return $xml;
}

/**
 * @session管理函数
 *
 * @详细配置
 * @param array $name
 * @return void
 *
 * @session管理
 * @param string $name session名称，'[start]', '[pause]', '[destory]', '[regenerate]'
 * @return void
 *
 * @获得session
 * @param string $name session名称，得到该名称的session值
 * @return mixed
 *
 * @查询session
 * @param string $name 带'?'前缀的session名称，查询是否存在该名称的session
 * @return bealoon
 *
 * @删除所有session
 * @param null
 * @return void
 *
 * @删除指定session
 * @param string $name session名称
 * @param null
 * @return void
 *
 * @设置一个session
 * @param string $name session名称
 * @param string|array 保存的内容
 * @return void
 *
 * @conf SESSION_PREFIX
 * @conf VAR_SESSION_ID sessionID的提交变量
 * @conf SESSION_TYPE
 * @conf SESSION_AUTO_START
 */
function session($name, $value = '') {

    // 获得前缀，默认空值
    $prefix = C('SESSION_PREFIX');

    // name是数组则表示使用详细配置，在session_start前先配置好session再启用
    // id, name, path, prefix, expire, domain, use_cookies, use_trans_sid,type
    if(is_array($name)) {

        // 如果设置了前缀，则替换conf
        if(isset($name['prefix'])) C('SESSION_PREFIX', $name['prefix']);

        // 根据默认值设置获得根据定义的id设置
        if(C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')]))
            session_id($_REQUEST[C('VAR_SESSION_ID')]);

        elseif(isset($name['id']))
            session_id($name['id']);

        // session 函数参考，http://www.php.net/manual/zh/ref.session.php
        // session runtime 配置参考，http://www.php.net/manual/zh/session.configuration.php
        ini_set('session.auto_start', 0);

        if(isset($name['name']))            session_name($name['name']);
        if(isset($name['path']))            session_save_path($name['path']);
        if(isset($name['domain']))          ini_set('session.cookie_domain', $name['domain']);
        if(isset($name['expire']))          ini_set('session.gc_maxlifetime', $name['expire']);
        if(isset($name['use_trans_sid']))   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
        if(isset($name['use_cookies']))     ini_set('session.use_cookies', $name['use_cookies']?1:0);
        if(isset($name['cache_limiter']))   session_cache_limiter($name['cache_limiter']);
        if(isset($name['cache_expire']))    session_cache_expire($name['cache_expire']);
        if(isset($name['type']))            C('SESSION_TYPE',$name['type']);

        // 如果存在其他session类型
        if(C('SESSION_TYPE')) {

            // 读取session驱动
            $class = 'Session'. ucwords(strtolower(C('SESSION_TYPE')));

            // 检查驱动类是否存在并加载，不存在则抛出错误
            if(Import::load(EXTEND_PATH.'Driver/Session/'.$class.'.class.php')) {

                $hander = new $class();
                $hander->execute();

            }else {
                // 类没有定义
                throw_exception(L('_CLASS_NOT_EXIST_').': ' . $class);
            }
        }

        // 启动session
        if(C('SESSION_AUTO_START'))  session_start();

    // 启用session管理
    } elseif('' === $value) {

        // session 操作
        if(0===strpos($name,'[')) {

            // 暂停session
            if('[pause]' == $name) {

                session_write_close();

            // 启动session
            } elseif('[start]' == $name) {

                session_start();

            // 销毁session
            } elseif('[destroy]' == $name) {

                $_SESSION =  array();
                session_unset();
                session_destroy();

            // 重新生成id
            } elseif('[regenerate]' == $name) {

                session_regenerate_id();
            }

        // 检查session是否存在，以?起头
        } elseif(0 === strpos($name, '?')) {

            $name = substr($name, 1);

            if($prefix)
                return isset($_SESSION[$prefix][$name]);
            else
                return isset($_SESSION[$name]);

        // 清空所有的session
        } elseif(is_null($name)) {

            if($prefix)
                unset($_SESSION[$prefix]);
            else
                $_SESSION = array();

        // 获取session，当存在前缀和不存在前缀的情况下
        } elseif($prefix) {

            return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;

        } else {

            return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        }

    // 删除session
    } elseif(is_null($value)) {

        if($prefix)
            unset($_SESSION[$prefix][$name]);
        else
            unset($_SESSION[$name]);

    // 设置session
    } else {

        if($prefix) {

            if(!is_array($_SESSION[$prefix])) $_SESSION[$prefix] = array();
            $_SESSION[$prefix][$name] = $value;

        } else {

            $_SESSION[$name] = $value;
        }
    }
}

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function cookie($name, $value='', $option=null) {
    // 默认设置
    $config = array(
        'prefix'    =>  C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire'    =>  C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'      =>  C('COOKIE_PATH'), // cookie 保存路径
        'domain'    =>  C('COOKIE_DOMAIN'), // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }
    $name = $config['prefix'] . $name;
    if ('' === $value) {
        if(isset($_COOKIE[$name])){
            $value =    $_COOKIE[$name];
            if(0===strpos($value,'think:')){
                $value  =   substr($value,6);
                return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
            }else{
                return $value;
            }
        }else{
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if(is_array($value)){
                $value  = 'think:'.json_encode(array_map('urlencode',$value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}

/**
 * 加载动态扩展文件
 * @return void
 */
function load_ext_file() {

    // 加载自定义外部文件
    if(C('LOAD_EXT_FILE')) {
        $files      =  explode(',',C('LOAD_EXT_FILE'));
        foreach ($files as $file){
            $file   = COMMON_PATH.$file.'.php';
            if(is_file($file)) include $file;
        }
    }

    // 加载自定义的动态配置文件
    if(C('LOAD_EXT_CONFIG')) {
        $configs    =  C('LOAD_EXT_CONFIG');
        if(is_string($configs)) $configs =  explode(',',$configs);
        foreach ($configs as $key=>$config){
            $file   = CONF_PATH.$config.'.php';
            if(is_file($file)) {
                is_numeric($key)?C(include $file):C($key,include $file);
            }
        }
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
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
function send_http_status($code) {
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

// 过滤表单中的表达式
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}