<?php
namespace Think\Behaviors;

use Think\Behavior as Behavior;
use Think\Cookie as Cookie;

// TODO: 重构Lang类，删除行为，直接添加在核心内
class CheckLang extends Behavior {

    // 行为参数定义（默认值） 可在项目配置中覆盖
    protected $options   =  array(
        'LANG_SWITCH_ON'        => on,           // 默认关闭语言包功能
        'LANG_AUTO_DETECT'      => true,            // 自动侦测语言 开启多语言功能后有效
        'LANG_LIST'             => 'zh-cn,en-us',   // 允许切换的语言列表 用逗号分隔
        'VAR_LANGUAGE'          => 'lang',          // 默认语言切换变量
    );

    // 行为扩展的执行入口必须是run
    public function run(&$params){

        // 开启静态缓存
        $this->checkLanguage();
    }

    /**
     * 语言检查
     * 检查浏览器支持语言，并自动加载语言包
     * @access private
     * @return void
     */
    private function checkLanguage() {

        // 不开启语言包功能，仅仅加载框架语言文件直接返回
        if(!C('LANG_SWITCH_ON')) {
            return;
        }

        // 默认语言
        $langSet = C('DEFAULT_LANG');

        // 启用语言包
        // 自动侦查语言
        if(C('LANG_AUTO_DETECT')) {

            // url中设置了语言变量
            if(isset($_GET[C('VAR_LANGUAGE')])){

                $langSet = $_GET[C('VAR_LANGUAGE')];
                Cookie::set('think_language',$langSet,3600);

            }
            // 获取上次用户的选择
            elseif(Cookie::get('think_language')) {

                $langSet = Cookie::get('think_language');

            // 自动侦测浏览器语言
            }elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

                preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = $matches[1];
                Cookie::set('think_language', $langSet, 3600);
            }

            // 非法语言参数，请用默认设置
            if(false === stripos(C('LANG_LIST'),$langSet)) {

                $langSet = C('DEFAULT_LANG');
            }
        }

        // 定义当前语言
        define('LANG_SET',strtolower($langSet));

        // echo LANG_PATH . LANG_SET . '/common.php';
        // 读取项目公共语言包
        if (is_file(LANG_PATH . LANG_SET . '/common.php')) {

            L(include LANG_PATH . LANG_SET . '/common.php');
        }

        $group = '';
        $lang_path = (C('APP_GROUP_MODE') == 1) ? BASE_LIB_PATH . 'Lang/' . LANG_SET . '/' : LANG_PATH . LANG_SET . '/';

        // 读取当前分组公共语言包
        if (defined('GROUP_NAME')){
            if (is_file($lang_path.GROUP_NAME.'.php'))
                L(include $lang_path.GROUP_NAME.'.php');

            $group = GROUP_NAME . C('TMPL_FILE_DEPR');
        }

        // 读取当前模块语言包
        if (is_file($lang_path . $group . strtolower(CONTROLLER_NAME) . '.php'))
            L(include $lang_path . $group . strtolower(CONTROLLER_NAME) . '.php');
    }
}