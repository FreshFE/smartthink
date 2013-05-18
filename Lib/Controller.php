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
use Think\Exception as Exception;

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
        Response::json($this->vars);
    }

    public function getModel($name)
    {
        return D($name);
    }

    /**
     * 析构方法
     * 根据配置来调整是否添加Log记录
     *
     * @return void
     */
    public function __destruct()
    {}
}