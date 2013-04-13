<?php
namespace Think\Behaviors;

use Think\Behavior as Behavior;
use Think\Debug as Debug;

class ParseTemplate extends Behavior {

    protected $options = array(
        // 模板引擎的类型
        'TMPL_ENGINE_TYPE' => 'Smarty'
    );

    public function run(&$_data){

        // 调用第三方模板引擎解析和输出
        $class = 'Think\\Driver\\Template\\' . C('TMPL_ENGINE_TYPE');

        // 加载类
        if(class_exists($class)) {
            $tpl = new $class;
            $tpl->fetch($_data['file'], $_data['var']);
        }

        // 类没有定义
        else {
            Debug::throw_exception(L('_NOT_SUPPERT_') . ':' . $class);
        }
    }
}