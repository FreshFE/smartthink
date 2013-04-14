<?php
namespace Think;
/**
 * meSmart php
 * Copyright (c) 2004-2013 Methink
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/meSmart
 * @package       meSmart.Views.Smarty
 * @since         meSmart php 0.1.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use \Smarty;

/**
 * 实现View接口
 * 采用Smarty作为该类的模板引擎，并简单配置，提供些接口供开发者修改
 * 开发者可以继承该文件，重新配置Smarty引擎
 * 继承后，记得在Mapping中修改$view所使用的类名称
 */
class View {

	/**
	 * 实例化模板引擎文件的存放属性
	 *
	 * @var object
	 */
	public $engine;

	/**
	 * 构造函数
	 * 先载入类包，再实例化Smarty，并执行配置Smarty
	 */
	public function __construct()
	{
		// 载入
		$this->import();

		// 实例化
		$this->engine = new Smarty();

		// 配置
		$this->config();
	}

	/**
	 * 载入Smarty
	 * 开发者可以在继承时重写这个函数以来达到改变载入路径的目的
	 *
	 * @return void
	 */
	protected function import()
	{
		include_once CORE_PATH . 'Views/Smarty/Smarty.class.php';
	}

	/**
	 * 对Smarty进行配置
	 *
	 * @return void
	 */
	protected function config()
	{
		// 是否开启缓存, 模板目录, 编译目录, 缓存目录
		$this->engine->caching           = defined('APP_DEBUG') ? APP_DEBUG : false;
		$this->engine->template_dir      = GROUP_PATH . 'Tpl/';
		$this->engine->compile_dir       = CACHE_PATH;
		$this->engine->cache_dir         = TEMP_PATH;
		$this->engine->debugging         = defined('TEMPLATE_DEBUG') ? TEMPLATE_DEBUG : false;
		$this->engine->left_delimiter    = '{{';
		$this->engine->right_delimiter   = '}}';
	}

	/**
	 * 结束程序，直接输出，同时也关系到缓存等问题
	 *
	 * @param string $name
	 * @param array $vars
	 */
	public function display($name, $vars)
	{
		$name = $this->parse_tpl($name);
		$this->engine->assign($vars);
		$this->engine->display($name);
	}

	/**
	 * 渲染模板，返回字符串
	 *
	 * @param string $name
	 * @param array $vars
	 * @return string
	 */
	public function fetch($name, $vars)
	{
		$name = $this->parse_tpl($name);
		$this->engine->assign($vars);
		return $this->engine->fetch($name);
	}

	public function parse_tpl($name = null)
	{
		// 为空
		if(is_null($name))
		{
			$name = CONTROLLER_NAME . '/' . ACTION_NAME;
		}
		// 当仅有一位时
		else {
			$names = explode('/', $name);

			if(count($names) === 1)
			{
				$name = CONTROLLER_NAME . '/' . $name;
			}
		}

		// 添加后缀
		return $name . '.html';
	}
}