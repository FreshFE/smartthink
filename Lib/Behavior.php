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

/**
 * 行为的父类，抽象方法，供其他行为继承
 * 设计了$this->options覆盖Config
 * 设计了run方法作为唯一执行入口
 */
abstract class Behavior
{
    /**
     * 行为参数
     * 在run运行时会将这里的参数和Config内参数进行匹配
     * 若Config内不存在改参数，则将此处参数赋值于Config
     *
     * @var array
     */
    protected $options = array();

    /**
     * 架构函数
     * 遍历合并Config
     *
     * @return void
     */
    public function __construct()
    {
        if(!empty($this->options))
        {
            foreach($this->options as $name => $val)
            {
                // 参数已设置 则覆盖行为参数
                if(Config::get($name) !== null)
                {
                    $this->options[$name] = C($name);
                }
                // 参数未设置 则传入默认值到配置
                else {
                    Config::set($name,$val);
                }
            }

            array_change_key_case($this->options);
        }
    }
    
    /**
     * 魔术方法 获取行为参数
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->options[strtolower($name)];
    }

    /**
     * 抽象方法，由子类实现作为该类的唯一执行入口
     *
     * @param mixed &$params
     * @return void
     */
    abstract public function run(&$params);
}