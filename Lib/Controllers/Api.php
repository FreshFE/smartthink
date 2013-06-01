<?php namespace Think\Controllers;

use Think\Controller as Controller;
use Think\Lang as Lang;

/**
 * Api Controller
 * 后台开发CURD及页面控制器
 */
class Api extends Controller {

	/**
	 * 操作成功返回 JSON
	 *
	 * @param array|null $output 输出值
	 * @param array $merge 合并值
	 * @return void
	 */
	public function successJson($output = null, $merge = array())
	{
		// 成功
		$this->assign('success', 1);

		// 是否存在输出
		if(!is_null($output)) {
			$this->assign('data', $output);
		}

		// 合并额外设置
		$this->vars = array_merge($this->vars, $merge);

		$this->json();
	}

	/**
	 * 操作失败返回 JSON
	 *
	 * @param string|Exception $error
	 * @return void
	 */
	public function errorJson($error)
	{
		$this->assign('success', 0);

		if(is_object($error)) {
			$this->assign('error', $error->getMessage());
			$this->assign('error_msg', Lang::get($error->getMessage()));
		}
		else if(is_string($error)) {
			$this->assign('error', $error);
			$this->assign('error_msg', Lang::get($error));
		}

		$this->json();
	}
}