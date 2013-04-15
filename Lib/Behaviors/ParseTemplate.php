<?php namespace Think\Behaviors;

use Think\Behavior as Behavior;
use Think\Debug as Debug;
use Think\Config as Config;
use \Exception;

class ParseTemplate extends Behavior {

    protected $options = array(
        // --------------------------------------------------
        // 默认模板引擎，改用Smarty
        // --------------------------------------------------
        'TMPL_ENGINE_DRIVER' => 'Think\\Views\\Smarty'
    );

    /**
     * 行为入口
     *
     * @param &$_data
     * @return void
     */
    public function run(&$_data){

        try {
            // 配置引擎
            $_content           = empty($_data['content']) ? $_data['file'] : $_data['content'];
            $_data['prefix']    = !empty($_data['prefix']) ? $_data['prefix'] : C('TMPL_CACHE_PREFIX');

            // 调用第三方模板引擎解析和输出
            $class = Config::get('TMPL_ENGINE_DRIVER');

            // 加载类
            if(class_exists($class))
            {
                $tpl = new $class;
                $tpl->fetch($_content, $_data['var']);
            }
            // 类没有定义
            else {
                throw new Exception(L('_NOT_SUPPERT_').': ' . $class);
            }
        }
        catch(Exception $error) {
            exit($error->getMessage());
        }
    }
}