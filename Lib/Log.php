<?php
namespace Think;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use Think\Response as Response;
use \KLogger;

/**
 * 日志处理类
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class Log {

    public static $storage;

    public static function init()
    {
        if(is_null($storage))
        {
            Import::load(CORE_PATH . 'Logs/KLogger.php');
            static::$storage = KLogger::instance(LOG_PATH, KLogger::DEBUG);
        }

        return static::$storage;
    }

    public static function info($line, $output = null)
    {
        $log = static::init();

        if(!is_null($output))
        {
            $log->logInfo('line', $output);
        }
        else {
            $log->logInfo('line');
        }
    }
}