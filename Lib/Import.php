<?php
namespace Think;

class Import {

	/**
	 * 带缓存导入
	 */
	public static function load($filename)
	{
		// 静态缓存
	    static $_storage = array();
	    
	    // 获得文件的真实路径
	    $filename = realpath($filename);

	    // 检查是否存在缓存
	    if(!isset($_storage[$filename]))
	    {
	    	// 未载入过，载入
	        if(File::exists_case($filename))
	        {
	            require $filename;
	            $_storage[$filename] = true;
	        }
	        // 已经载入过
	        else
	        {
	            $_storage[$filename] = false;
	        }
	    }

	    return $_storage[$filename];
	}

	/**
	 * 批量加载文件
	 * 内部批量调用load方法
	 *
	 * @param array $files 文件名数组
	 * @param bealoon $return 是否需要返回 !!!继续研究是否有存在该参数和功能的必要
	 *
	 * @return bealoon
	 */
	public static function loads($files)
	{
	    foreach ($files as $file)
	    {
	        static::load($file);
	    }
	}

	/**
	 * 加载控制器
	 *
	 * @param string $name 控制器名称
	 * @return object | false
	 */
	public static function controller($groupName, $controllerName)
	{
		// 定义控制器命名
		$class = "App\\" . $groupName . "\\Controller\\" . $controllerName . "Controller";

		// 是否存在该类
		if(class_exists($class)) {
			return new $class;
		}
		else {
			return false;
		}
	}
}