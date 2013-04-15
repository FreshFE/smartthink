<?php namespace Think\Views;
/**
 * SmartThink PHP
 * Copyright (c) 2004-2013 Methink
 * Thanks for ThinkPHP & GEM-MIS
 * @copyright     Copyright (c) Methink
 * @link          http://smartthink.org
 * @package       Think.Views.Smarty
 * @since         SmartThink 1.0.0 & ThinkPHP 3.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use \Smarty as SmartyEngine;
use Think\Import as Import;
use Think\Config as Config;

/**
 * Smarty模板引擎的驱动
 */
class Smarty {

    /**
     * 被调用接口
     * 载入Smarty模板引擎，获得相关内容
     */
    public function fetch($templateFile, $var)
    {
        // 载入Smarty文件
        include_once CORE_PATH . 'Views/Smarty/Smarty.class.php';

        // 实例化
        $tpl = new SmartyEngine();

        // 是否开启缓存, 模板目录, 编译目录, 缓存目录
        $tpl->caching           = Config::get('TMPL_CACHE_ON');
        $tpl->template_dir      = THEME_PATH;
        $tpl->compile_dir       = CACHE_PATH;
        $tpl->cache_dir         = TEMP_PATH;
        $tpl->debugging         = false;
        $tpl->left_delimiter    = '{{';
        $tpl->right_delimiter   = '}}';

        // 自定义配置        
        if(C('TMPL_ENGINE_CONFIG')) {
            $config  =  C('TMPL_ENGINE_CONFIG');
            foreach ($config as $key => $val){
                $tpl->{$key} = $val;
            }
        }

        // 输出
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
}