<?php

class Import {

	/**
	 * 带缓存导入
	 */
	public static function load($filename) {

	    static $_files = array();
	    
	    $filename = realpath($filename);

	    if(!isset($_files[$filename])) {

	    	// 未载入过，载入
	        if(static::file_exists_case($filename)) {

	            require $filename;
	            $_files[$filename] = true;
	        }

	        // 已经载入过
	        else {
	            $_files[$filename] = false;
	        }
	    }

	    return $_files[$filename];
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
	public static function loads($files) {

	    foreach ($files as $file) {
	        static::load($file);
	    }
	}

	/**
	 * 检查该文件是否存在大小写版本
	 * 针对windows平台做优化
	 *
	 * @param string $filename 检查的文件路径
	 * @return bealoon
	 */
	public static function file_exists_case($filename) {

		// 该文件是否存在
	    if (is_file($filename)) {

	    	// windows平台下并开启大小写检查
	        if (IS_WIN && C('APP_FILE_CASE')) {
	            if (basename(realpath($filename)) != basename($filename)) {
	            	return false;
	            }
	        }
	        return true;
	    }
	    return false;
	}
}