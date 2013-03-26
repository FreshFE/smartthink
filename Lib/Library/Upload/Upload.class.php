<?php namespace Think\Library;

\Import::uses('UploadFile', dirname(__FILE__));

class Upload {

	public static function image($image, $thumbTypeNames) {

		// 路径和文件名
		$imagepath = date('Y') . '/' . date('W') . '/';
		$basePath = './upload/images/';

		// 实例化
		$handle = new UploadFile(array(

				// 基本
				'maxSize'			=> 4 * pow(1024, 3),
				'allowExts' 		=> array('jpg', 'jpeg', 'png', 'gif'),

				// 保存路径
				'basePath'			=> $basePath,
				'typePath'			=> 'o/',
				'subPath' 			=> $imagepath,

				// 缩略图设置
				'uploadReplace' 	=> true
			));

		// 如果需要生成缩略图
		if(!empty($thumbTypeNames)) {

			$handle->thumb = true;
			$handle->thumbTypeNames = $thumbTypeNames;
		}

		// 执行上传操作
		$status = $handle->upload($image);

		// 如果上传错误，返回错误信息
		if(!$status)
			return $handle->getErrorMsg();

		// 插入数据库
		$status['id'] = M('Image')->add($status);

		return $status;
	}
}