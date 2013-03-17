#Smart ThinkPHP

##Thanks ThinkPHP and Smarty

##1.0.0

###与ThinkPHP不同的地方

1. 主要继承了ThinkPHP的源代码，保留大部分ThinkPHP功能
2. 移除了ThinkPHP的ThinkTemplate模板引擎
3. 生成的url设定为小写
4. 添加了加载Extend/Conf的机制
5. 添加了加载Extend/Function的机制

###Smart ThinkPHP不合理地方

1. 未删减对Extend的依赖性，导致必须同时使用Extend文档

##2.0.0 计划

版本目的：彻底改变ThinkPHP的包管理方式

1. 整理加载函数，import, import_alias, load, vendor等
2. 将Common下的common.php和functions.php内的函数调整到class类内
3. 废弃原Extend拓展的加载方式，使用PHP composer方式加载和管理
4. ...

##安装Smart ThinkPHP

**PS: 看该安装方式可能会比较模糊**

###下载框架和拓展
	
	git clone https://github.com/minowu/thinkphp.git
	git clone https://github.com/minowu/extend.git

###根据ThinkPHP入口文件的方式，编辑index.php内容

	// 调试模式
	define('APP_DEBUG', true);

	// 项目核心
	define('ENTRY_PATH', './');

	define('APP_PATH', '../App/');
	define('THINK_PATH', '../../framework/thinkphp/');
	define('EXTEND_PATH', '../../framework/extend/');

	// 加载框架并运行
	require THINK_PATH . 'ThinkPHP.php';
