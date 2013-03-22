	// 记录和统计时间（微秒）和内存使用情况
	G();

	// 设置和获取统计数据
	N();

	// 字符串命名风格转换
	parse_name();

	// 优化的require_once
	require_cache();

	// 批量导入文件 成功则返回
	require_array();

	// 区分大小写的文件存在判断
	file_exists_case();

	// 导入所需的类库 同java的Import 本函数有缓存功能
	import();

	// 基于命名空间方式导入函数库
	load();

	// 快速导入第三方框架类库
	vendor();

	// 快速定义和导入别名 支持批量定义
	alias_import();

	// D函数用于实例化Model
	D();

	// M函数用于实例化一个没有模型的Model
	M();

	// A函数用于实例化Action
	A();

	// 远程调用模块的操作方法
	R();

	// 获取和设置语言定义
	L();

	// 获取和设置配置参数
	C();

	// 处理标签扩展
	tag();

	// 动态添加行为扩展到某个标签
	add_tag_behavior();

	// 执行某个行为
	B();

	// 去除代码中的空白和注释
	strip_whitespace();

	// 编译文件
	compile();

	// 根据数组生成常量定义
	array_define();

	// 添加和获取页面Trace记录
	trace();