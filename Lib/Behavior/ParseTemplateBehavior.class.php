<?php

class ParseTemplateBehavior extends Behavior {

    protected $options = array(
        // 模板引擎的类型
        'TMPL_ENGINE_TYPE' => 'Smarty'
    );    

    public function run(&$_data){

        // 配置引擎
        $engine             = strtolower(C('TMPL_ENGINE_TYPE'));
        $_content           = empty($_data['content']) ? $_data['file'] : $_data['content'];
        $_data['prefix']    = !empty($_data['prefix']) ? $_data['prefix'] : C('TMPL_CACHE_PREFIX');

        // 调用第三方模板引擎解析和输出
        $class   = 'Template'.ucwords($engine);

        // 加载类
        if(class_exists($class)) {

            $tpl = new $class;
            $tpl->fetch($_content, $_data['var']);
        }

        // 类没有定义
        else {

            throw_exception(L('_NOT_SUPPERT_').': ' . $class);
        }
    }
}