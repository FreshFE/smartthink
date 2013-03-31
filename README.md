#meSmart php

##Thanks ThinkPHP and Smarty

##2.0.0

###改变了原ThinkPHP的结构和扩展方式

	thinkphp
		|
		|-----Conf
		|		|-----convention.php
		|		|-----debug.php
		|		|-----tags.php
		|
		|-----Lang
		|-----Lib
		|		|-----Behavior
		|		|-----Core
		|		|-----Driver
		|		|-----Library
		|		|-----View
		|
		|-----Tpl
		|-----functions.php
		|-----meSmart.php

---
###删除ThinkPHP原模板引擎，彻底改用Smarty
###删除ThinkPHP原Runtime在生成模式下的编译功能
###新建Import, Redirect类
###将Action彻底改名为Controller
###彻底删除原Extend加载机制
###删除别名功能
###删除所有非分组和跨项目的判断代码
###类彻底模块化和按需加载
###重构widget