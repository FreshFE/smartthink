#Smart ThinkPHP

##Thanks ThinkPHP and Smarty

##2.0.0

###改变了原ThinkPHP的结构和扩展方式

	thinkphp
		|-----Common
		|		|-----common.php 	// 继续删减成类
		|		|-----functions.php // 彻底改变
		|
		|-----Conf					// 删除别名功能
		|-----Lang
		|-----Lib
		|		|-----Behavior
		|		|-----Core
		|		|-----Driver
		|		|-----Library
		|		|-----View
		|
		|-----Tpl

---
###删除ThinkPHP原模板引擎，彻底改用Smarty
###删除ThinkPHP原Runtime在生成模式下的编译功能
###新建Import, Redirect类
###将Action彻底改名为Controller
---
###[ing]彻底删除原Extend加载机制
###[ing]删除别名功能
###[ing]删除所有非分组和跨项目的判断代码
###[ing]类彻底模块化和按需加载
###[ing]重构widget