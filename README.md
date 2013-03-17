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