<?php
namespace Think\Behaviors;

use Think\Behavior as Behavior;
use Think\Debug as Debug;

class WriteHtmlCache extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        if(C('HTML_CACHE_ON') && defined('HTML_FILE_NAME'))  {
            //静态文件写入
            // 如果开启HTML功能 检查并重写HTML文件
            // 没有模版的操作不生成静态文件
            if(!is_dir(dirname(HTML_FILE_NAME)))
                mkdir(dirname(HTML_FILE_NAME),0755,true);
            if( false === file_put_contents( HTML_FILE_NAME , $content ))
                Debug::throw_exception(L('_CACHE_WRITE_ERROR_').':'.HTML_FILE_NAME);
        }
    }
}