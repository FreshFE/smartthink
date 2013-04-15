<?php namespace Think;
/**
 * SmartThink PHP
 * Copyright (c) 2004-2013 Methink
 * Thanks for ThinkPHP & GEM-MIS
 * @copyright     Copyright (c) Methink
 * @link          http://smartthink.org
 * @package       Think.Behavior
 * @since         ThinkPHP 3.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use Think\Config as Config;
use Think\Log as Log;
use Think\View as View;
use Think\Response as Response;
use Think\File as File;
use Think\Debug as Debug;

/**
 * 控制器抽象类，供项目开发的控制器类继承
 * 内置了关于模板的视图操作调用方法，assign, display & fetch
 * 添加了json输出的快捷方法
 * TODO: 计划逐步删除__call方法里的内容
 */
abstract class Controller {

    /**
     * 实例化后的视图类容器
     *
     * @var object
     */
    protected $view;

    /**
     * 存放备输出数组的容器
     * 由$this->assign()方法添加
     * 被display, fetch & json使用
     *
     * @var array
     */
    protected $vars = array();

    /**
     * 构造函数
     * 实例化视图类
     *
     * @return void
     */
    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * 将要输出的内容存入$this->vars
     *
     * @stability: 4
     * @param string|array $name
     * @param mixed $value
     * @return void
     */
    protected function assign($name, $value)
    {
        if(is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        }
        else if(!is_null($value)) {
            $this->vars[$name] = $value;
        }
    }

    /**
     * 调用view的display接口
     * 将$this->vars输出到模板
     *
     * @stability: 4
     * @param string $name
     * @return void
     */
    protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        if($this->vars)
        {
            $this->view->assign($this->vars);
        }
        
        $this->view->display($templateFile, $charset, $contentType, $content, $prefix);
    }

    /**
     * 调用view的fetch接口
     * 将$this->vars输出到模板，并返回最后生成的字符串
     *
     * @stability: 4
     * @param string $name
     * @return string
     */
    protected function fetch($templateFile = '', $content = '', $prefix = '')
    {
        if($this->vars)
        {
            $this->view->assign($this->vars);
        }

        return $this->view->fetch($templateFile, $content, $prefix);
    }

    /**
     * 将$this->vars解析成json输出，并结束程序
     *
     * @stability: 3
     * @param string|array|null $name
     * @param mixed|null $value
     * @return void
     */
    protected function json($name = null, $value = null)
    {
        if(!is_null($name))
        {
            $this->assign($name, $value);
        }
        exit(json_encode($this->vars));
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,'_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method,$args);
            }elseif(File::exists_case(C('TEMPLATE_NAME'))){
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            }elseif(function_exists('__hack_action')) {
                // hack 方式定义扩展操作
                __hack_action();
            }else{
                Response::_404(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }else{
            switch(strtolower($method)) {
                // 判断提交方式
                case 'ispost'   :
                case 'isget'    :
                case 'ishead'   :
                case 'isdelete' :
                case 'isput'    :
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                // 获取变量 支持过滤和默认值 调用方式 $this->_post($key,$filter,$default);
                case '_get'     :   $input =& $_GET;break;
                case '_post'    :   $input =& $_POST;break;
                case '_put'     :   parse_str(file_get_contents('php://input'), $input);break;
                case '_param'   :  
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $input  =  $_POST;
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $input);
                            break;
                        default:
                            $input  =  $_GET;
                    }
                    if(C('VAR_URL_PARAMS')){
                        $params = $_GET[C('VAR_URL_PARAMS')];
                        $input  =   array_merge($input,$params);
                    }
                    break;
                case '_request' :   $input =& $_REQUEST;   break;
                case '_session' :   $input =& $_SESSION;   break;
                case '_cookie'  :   $input =& $_COOKIE;    break;
                case '_server'  :   $input =& $_SERVER;    break;
                case '_globals' :   $input =& $GLOBALS;    break;
                default:
                    Debug::throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            }
            if(!isset($args[0])) { // 获取全局变量
                $data       =   $input; // 由VAR_FILTERS配置进行过滤
            }elseif(isset($input[$args[0]])) { // 取值操作
                $data       =	$input[$args[0]];
                $filters    =   isset($args[1])?$args[1]:C('DEFAULT_FILTER');
                if($filters) {// 2012/3/23 增加多方法过滤支持
                    $filters    =   explode(',',$filters);
                    foreach($filters as $filter){
                        if(function_exists($filter)) {
                            $data   =   is_array($data)?array_map($filter,$data):$filter($data); // 参数过滤
                        }
                    }
                }
            }else{ // 变量默认值
                $data       =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }

    /**
     * 析构方法
     * 根据配置来调整是否添加Log记录
     *
     * @return void
     */
    public function __destruct()
    {
        if(Config::get('LOG_RECORD')) Log::save();
    }
}