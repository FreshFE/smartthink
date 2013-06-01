<?php namespace Think\Models;

use Think\Model as Model;

class Content extends Model {

	/**
	 * 被默认创建的数据
	 *
	 * var array
	 */
	protected $defaultData = array('title' => '未命名');

	/**
	 * 模型自动完成
	 *
	 * var array
	 */
	protected $_auto = array (

		// 是否公开
		array('hidden', 0, 1),

		// 系统记录时间
		array('updateline', 'time', 3, 'function'),
		array('createline', 'time', 1, 'function'),

		// 自定义时间
		array('customline', 'time', 1, 'function'),
		array('customline', 'strtotime', 2, 'function')
	);

	/**
	 * 创建默认数据
	 *
	 * @param int $cid
	 */
	public function createDefault($data) {

		// dump($data);
		// exit();

		// 需要被创建的默认数据
		$defaultData = array_merge($this->defaultData, $data);

		dump($defaultData);
		exit();

		// 关闭表单验证（临时方法）
		$token_on = C('TOKEN_ON');
		C('TOKEN_ON', false);

		// 创建数据，设置自动完成
		$data = $this->create($defaultData, 1);

		// 打开表单验证（临时方法）
		C('TOKEN_ON', $token_on);

		// 添加到数据库
		return $this->add();
	}

}