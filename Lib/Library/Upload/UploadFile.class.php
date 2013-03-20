<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * 文件上传类
 * @category    ORG
 * @package     ORG
 * @subpackage  Net
 * @author      liu21st <liu21st@gmail.com>
 * @author      minowu
 */

/*
array(11) {
    ["thumbname"] => string(12) "thumb,banner",varchar-50

    ["originalname"] => string(27) "Enkoji_Temple_II_976822.jpg",varchar-100
    ["type"] => string(10) "image/jpeg",char-10
    ["extension"] => string(3) "jpg",char-4
    ["size"] => int(679396),int

    ["basepath"] => string(16) "./upload/images/",char-16
    ["typepath"] => string(2) "o/",char-1
    ["subpath"] => string(8) "2012/50/",char-8
    ["savepath"] => string(26) "./upload/images/o/2012/50/",char-26
    ["saverule"] => string(13) "50c9f80f3efad",char-13

    ["name"] => string(25) "2012/50/50c9f80f3efad.jpg",char-26
}
*/

class UploadFile { 

    private $config =   array(

        // 基本
        'maxSize'           => -1,                  // 上传文件的最大值
        'supportMulti'      => true,                // 是否支持多文件上传
        'allowExts'         => array(),             // 允许上传的文件后缀 留空不作后缀检查
        'allowTypes'        => array(),             // 允许上传的文件类型 留空不做检查
        'uploadReplace'     => false,               // 存在同名是否覆盖

        // 路径和文件名
        'basePath'          => './upload/',         // 上传基本路径，./upload/images/
        'typePath'          => '',                  // o/
        'subPath'           => '',                  // 2012/50/
        'savePath'          => '',
        'saveRule'          => '',                  // 文件上传的名称，如果为空值则默认设置为uniqid

        // 缩略图
        'thumb'             => false,               // 使用对上传图片进行缩略图处理
        'imageClassPath'    => 'ORG.Util.Image',    // 图库类包路径
        'thumbRemoveOrigin' => false,               // 是否移除原图
        'thumbType'         => 'PROJ_THUMB_TYPE',   // 缩略图的默认配置参数
        'thumbTypeNames'    => 'thumb',             // 处理哪些缩略图类型

        // 其他
        'zipImages'         => false,               // 压缩图片文件上传
        'autoSub'           => false,               // 启用子目录保存文件
        'subType'           => 'hash',              // 子目录创建方式 可以使用hash date custom
        'subDir'            => '',                  // 子目录名称 subType为custom方式后有效
        'dateFormat'        => 'Ymd',
        'hashLevel'         => 1,                   // hash的目录层次
        'autoCheck'         => true,                // 是否自动检查附件
        'hashType'          => 'md5_file',          // 上传文件Hash规则函数名
    );

    // 私有配置
    private $error = '';            // 错误信息
    private $uploadFileInfo;        // 上传成功的文件信息
    private $fileInfo = array();    // 文件信息

    /**
     * @魔法方法
     * get, set and isset
     */
    public function __get($name){
        if(isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }

    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    public function __isset($name){
        return isset($this->config[$name]);
    }
    
    /**
     * 架构函数
     * @param array $config 参数配置
     */
    public function __construct($config = array()) {

        // 合并配置
        if(is_array($config)) $this->config = array_merge($this->config,$config);

        // 生成Savepath
        $this->savePath = $this->basePath . $this->typePath . $this->subPath;
    }

    /**
     * @上传单个文件
     * @param array $file 上传文件信息
     * @param string $savePath 上传文件保存路径
     * @return array
     */
    public function upload($file, $savePath = '') {

        //如果不指定保存文件名，则由系统默认
        if(empty($savePath))
            $savePath = $this->savePath;

        // 检查是否具有可读和可写性
        if(!$this->checkPath($savePath))
            return false;
        
        // 检查文件是否存在
        if(!empty($file['name'])) {

            // 登记上传文件的扩展信息
            $file['extension']  = $this->getExt($file['name']);
            $file['savepath']   = $savePath;
            $file['savename']   = $this->getSaveName($file);

            // 自动检查附件
            if($this->autoCheck) {
                if(!$this->check($file))
                    return false;
            }

            //保存上传文件
            if(!$this->save($file)) return false;

            // 删除临时文件
            unset($file['tmp_name'], $file['error']);

            // 返回上传的文件信息
            $this->fileInfo = array_merge($this->fileInfo, array(
                'originalname'  => $file['name'],
                'type'          => $file['type'],
                'extension'     => $file['extension'],
                'size'          => $file['size']
            ));

            // 返回文件信息
            return $this->getFileInfo();

        }else {

            $this->error = '没有选择上传文件';
            return false;
        }
    }

    /**
     * @转化接口
     */
    public function uploadOne($file, $savePath = '') {

        return $this->upload($file, $savePath);
    }

    /**
     * 保存一个文件
     * @access public
     * @param mixed $name 数据
     * @return bealoon
     */
    private function save($file) {

        $filename = $file['savepath'].$file['savename'];

        // 检测是否覆盖同名文件
        if(!$this->uploadReplace && is_file($filename)) {
            
            $this->error = '文件已经存在！' . $filename;
            return false;
        }

        // 如果是图像文件，检测文件格式
        if( in_array(strtolower($file['extension']), array('gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'))) {

            $info = getimagesize($file['tmp_name']);

            if(false === $info || ('gif' == strtolower($file['extension']) && empty($info['bits']))){

                $this->error = '非法图像文件';
                return false;                
            }
        }

        // 将临时文件保存至指定目录
        if(!move_uploaded_file($file['tmp_name'], $this->autoCharset($filename, 'utf-8', 'gbk'))) {

            $this->error = '文件上传保存错误！';
            return false;
        }

        // 检测是否生成缩略图并且是图像格式
        if($this->thumb && in_array(strtolower($file['extension']), array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {

            $image = getimagesize($filename);

            if(false !== $image) {

                // 导入图片类包
                import($this->imageClassPath);

                // 获得参数
                $thumbType = C($this->thumbType);
                $thumbPath = dirname($filename);
                $thumbTypeNames = explode(',', $this->thumbTypeNames);

                // 预设数组
                $thumbnameArray = array();

                // 遍历生成缩略图
                for($i = 0, $len = count($thumbTypeNames); $i<$len; $i++) {

                    $thumbTypeName = $thumbTypeNames[$i];
                    $config = $thumbType[$thumbTypeName];

                    // 计算最大宽度
                    $maxWidth = $config[0];
                    $maxHeight = $config[1];
                    $mod = $config[2];

                    // 生成文件名，原图路径，缩略图类型名，原图文件名
                    // $thumbname = $thumbPath . '/' . $thumbTypeName . '_' . basename($filename);

                    // $thumbPath = './upload/images/' . $thumbTypeName . '/2012/50/';
                    $thumbPath = $this->basePath . $thumbTypeName . '/' . $this->subPath;
                    $thumbName = $thumbPath . basename($filename);

                    // 检查是否具有可读和可写性
                    if(!$this->checkPath($thumbPath))
                        return false;
                    
                    // 生成缩略图
                    // 图像地址，缩略图路径和名称，最大宽度，最大高度，模式，拓展名类型
                    $result = Image::thumb($filename, $thumbName, $maxWidth, $maxHeight, $mod);

                    if($result)
                        $thumbnameArray[] = $thumbTypeName;
                }

                // $this->fileInfo = array_merge($this->fileInfo, );
                $this->fileInfo['thumbname'] = join($thumbnameArray, ',');

                // 生成缩略图之后删除原图
                if($this->thumbRemoveOrigin) {
                    unlink($filename);
                }
            }
        }

        return true;
    }

    /**
     * @检测目录是否存在，并且是否具有可写性
     * @param string $savePath 保存路径
     * @return bealoon
     */
    private function checkPath($savePath) {

        if(!is_dir($savePath)) {

            // 尝试创建目录
            if(!mkdir($savePath, 0777, true)){
                $this->error = '目录' . $savePath . '不存在';
                return false;
            }
        }else {

            if(!is_writeable($savePath)) {
                $this->error = '目录' . $savePath . '不可写';
                return false;
            }
        }

        return true;
    }

    /**
     * 获取错误代码信息
     * @access public
     * @param string $errorNo  错误号码
     * @return void
     */
    protected function error($errorNo) {

         switch($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return ;
    }

    private function getSaveName($filename) {

        if(empty($this->saveRule))
            $this->saveRule = uniqid();

        return $this->saveRule . '.' . $filename['extension'];
    }

    /**
     * 检查上传的文件
     * @access private
     * @param array $file 文件信息
     * @return boolean
     */
    private function check($file) {
        if($file['error']!== 0) {
            //文件上传失败
            //捕获错误代码
            $this->error($file['error']);
            return false;
        }
        //文件上传成功，进行自定义规则检查
        //检查文件大小
        if(!$this->checkSize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        //检查文件Mime类型
        if(!$this->checkType($file['type'])) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }
        //检查文件类型
        if(!$this->checkExt($file['extension'])) {
            $this->error ='上传文件类型不允许';
            return false;
        }

        //检查是否合法上传
        if(!$this->checkUpload($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }
        return true;
    }

    // 自动转换字符集 支持数组转换
    private function autoCharset($fContents, $from='gbk', $to='utf-8') {
        $from   = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to     = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    }

    /**
     * 检查上传的文件类型是否合法
     * @access private
     * @param string $type 数据
     * @return boolean
     */
    private function checkType($type) {
        if(!empty($this->allowTypes))
            return in_array(strtolower($type),$this->allowTypes);
        return true;
    }


    /**
     * 检查上传的文件后缀是否合法
     * @access private
     * @param string $ext 后缀名
     * @return boolean
     */
    private function checkExt($ext) {
        if(!empty($this->allowExts))
            return in_array(strtolower($ext),$this->allowExts,true);
        return true;
    }

    /**
     * 检查文件大小是否合法
     * @access private
     * @param integer $size 数据
     * @return boolean
     */
    private function checkSize($size) {
        return !($size > $this->maxSize) || (-1 == $this->maxSize);
    }

    /**
     * 检查文件是否非法提交
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function checkUpload($filename) {
        return is_uploaded_file($filename);
    }

    /**
     * 取得上传文件的后缀
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function getExt($filename) {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }

    /**
     * 取得上传文件的信息
     * @access public
     * @return array
     */
    public function getUploadFileInfo() {
        
        $info = $this->uploadFileInfo;

        $info['typePath'] = $this->typePath;
        $info['filePath'] = $info['imagepath'] . $info['savename'];

        return $info;
    }

    /**
     * 获得文件保存的信息
     * @return array
     */
    public function getFileInfo() {

        // 获得fileinfo
        $fileInfo = $this->fileInfo;

        // 合并fileinfo
        $fileInfo = array_merge($fileInfo, array(
            'basepath'  => $this->basePath,
            'typepath'  => $this->typePath,
            'subpath'   => $this->subPath,
            'savepath'  => $this->savePath,
            'saverule'  => $this->saveRule,

            'name'      => $this->subPath.$this->saveRule.'.'.$fileInfo['extension'],

            'updateline' => time(),
            'createline' => time()
        ));

        return $this->fileInfo = $fileInfo;
    }

    /**
     * 取得最后一次错误信息
     * @access public
     * @return string
     */
    public function getErrorMsg() {
        return $this->error;
    }
}