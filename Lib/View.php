<?php namespace Think;

use Think\Debug as Debug;
use Think\Tag as Tag;

/**
 * view视图类
 * 主要接口是调用模板引擎类的fetch，display使用自身配置
 * 该类主要是使用了ThinkPHP的代码
 */
class View
{
	/**
	 * 存放待输出变量的数组
	 *
	 * @var array
	 */
    protected $tVar = array();

    /**
     * 添加如$this->tVar值
     *
     * @param string|array $name
     * @param value|null $value
     * @return void
     */
    public function assign($name, $value = '')
    {
        if(is_array($name))
        {
            $this->tVar = array_merge($this->tVar, $name);
        }
        else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * 获得$this->tVar的值
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if(is_null($name))
        {
            return $this->tVar;
        }
        else {
        	return isset($this->tVar[$name]) ? $this->tVar[$name] : false;
        }
    }

    /**
     * 将html字符串输出返回给游览器
     * 内部调用fetch和render方法，而非直接使用模板引擎的方法
     *
     */
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
    	// 标记统计
        Debug::mark('viewStartTime');

        // 视图开始标签
        Tag::listen('view_begin', $templateFile);

        // 解析并获取模板内容
        $content = $this->fetch($templateFile, $content, $prefix);

        // 输出模板内容
        $this->render($content, $charset, $contentType);

        // 视图结束标签
        Tag::listen('view_end');
    }

    /**
     * 将内容输出到游览器
     *
     */
    private function render($content, $charset = '', $contentType = '')
    {
    	// 默认字符编码
        if(empty($charset))
        {
        	$charset = Config::get('DEFAULT_CHARSET');
        }

        // 默认文件类型
        if(empty($contentType))
        {
        	$contentType = Config::get('TMPL_CONTENT_TYPE');
        }

        // 输出header头
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: '.Config::get('HTTP_CACHE_CONTROL'));
        header('X-Powered-By: SmartThink');
        header('X-Thanks: Thanks for ThinkPHP, Lavarel, Smarty & Composer');
        header('X-Develop-Team: http://smartthink.org');

        // 打印内容
        echo $content;
    }

    /**
     * 从模板引擎处获得内容
     */
    public function fetch($templateFile = '', $content = '', $prefix = '')
    {
        if(empty($content))
        {
            // 模板文件解析标签
            Tag::listen('view_template',$templateFile);

            // 模板文件不存在直接返回
            if(!is_file($templateFile))
            {
            	return null;
            }
        }

        // 页面缓存
        ob_start();
        ob_implicit_flush(0);

        // 使用PHP原生模板
        if('php' == strtolower(Config::get('TMPL_ENGINE_TYPE')))
        {
            // 模板阵列变量分解成为独立变量
            extract($this->tVar, EXTR_OVERWRITE);

            // 直接载入PHP模板
            empty($content) ? include $templateFile : eval('?>' . $content);
        }
        else {
            // 视图解析标签
            $params = array(
            	'var' => $this->tVar,
            	'file' => $templateFile,
            	'content' => $content,
            	'prefix' => $prefix
            );

            // 解析模板
            Tag::listen('view_parse',$params);
        }

        // 获取并清空缓存
        $content = ob_get_clean();

        // 内容过滤标签
        Tag::listen('view_filter',$content);

        // 返回内容
        return $content;
    }
}