<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 系统默认的核心行为扩展列表文件
return array(
    'app_init'      =>  array(
    ),
    'app_begin'     =>  array(
        'ReadHtmlCache', // 读取静态缓存
        'CheckLang' // 读取Lang
        
    ),
    'app_auth' => array(
        'CheckAuth'
    ),
    'route_check'   =>  array(
        'CheckRoute', // 路由检测
    ), 
    'app_end'       =>  array(),
    'path_info'     =>  array(),
    'view_filter'   =>  array(
        'ContentReplace', // 模板输出替换
        'TokenBuild',   // 表单令牌
        // 'WriteHtmlCache', // 写入静态缓存
        'ShowRuntime', // 运行时间显示
    ),
    'view_end'      =>  array(
        'ShowPageTrace', // 页面Trace显示
    ),
);