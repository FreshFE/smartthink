<?php

// -------------------------------------------
// 自动载入函数
// -------------------------------------------
spl_autoload_register(function($classname)
{
	// 定义别名路径
	$alias = array(
		'App' => LIB_PATH,
		'Think' => CORE_PATH,
		'Smartadmin' => VENDOR_PATH . 'smartadmin/'
	);

	// 遍历别名路径
	foreach ($alias as $key => $path)
	{
		if(strpos($classname, $key) === 0)
		{
			// 根据名字该路径
			$filename = $path . ltrim(str_replace('\\', '/', $classname), $key);
			$return = true;
			break;
		}
	}

	// 没有被alias改写
	if(!$return) {
		$filename = VENDOR_PATH . str_replace('\\', '/', $classname);
	}

	// 加载文件
   include_once $filename . EXT;
});