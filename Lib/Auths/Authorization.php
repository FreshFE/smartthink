<?php namespace Think\Auths;

class Authorization
{
	protected $userRole;

	protected $roleRules;

	public function __construct($userRole, $roleRules)
	{
		$this->userRole = $userRole;
		$this->roleRules = $roleRules;
	}

	public function check()
	{
		$rule = $this->getRuleByUser();

		$routers = array(
			GROUP_NAME . ':' . CONTROLLER_NAME . ':' . ACTION_NAME,
			GROUP_NAME . ':' . CONTROLLER_NAME,
			GROUP_NAME
		);

		foreach ($routers as $router) {
			
			$access = $rule[$router];

			// 数组的话，执行指定类方法
			if(is_array($access)) {

				// 执行类
				$class = $access[0];

				// 实例化
				$object = new $class();

				// 执行
				$result = $object->run();

				// 返回
				if($result) {
					return $access[1];
				}
			}

			// 布尔值的话，返回
			if(is_bool($access)) {

				return $access;
			}
		}

		return false;
	}

	public function getRuleByUser()
	{
		$roleRules = $this->roleRules;
		$rule = $roleRules[$this->userRole];

		// 没有角色继承模型
		if(!isset($rule['_extends'])) {
			return $rule;
		}

		// 合并角色继承
		$extends = $rule['_extends'];
		unset($rule['_extends']);
		$temp = array();

		foreach ($extends as $key => $value) {
			$temp = array_merge($temp, $roleRules[$value]);
		}

		return array_merge($temp, $rule);
	}
}