<?php

return  array(

    // -------------------------------------------
    // 项目配置
    // -------------------------------------------
    /**
     * TODO: 可以废弃
     * 应用调试模式状态
     * 调试模式开启后有效
     * 默认为debug，可扩展，并自动加载对应的配置文件
     */
    'APP_STATUS'            => 'debug',

    /**
     * 是否检查文件的大小写，对Windows平台有效
     */
    'APP_FILE_CASE'         => true,


    /**
     * 自动加载机制的自动搜索路径,注意搜索顺序
     */
    /*'APP_AUTOLOAD_PATH'     => '',*/

    /**
     * TODO: 合并源代码后删除
     * 系统标签扩展开关
     */
    'APP_TAGS_ON'           => true,

    /**
     * 1. 是否开启子域名部署
     * 2. 子域名部署规则
     * 3. 子域名禁用列表
     */
    'APP_SUB_DOMAIN_DEPLOY' => false,
    'APP_SUB_DOMAIN_RULES'  => array(),
    'APP_SUB_DOMAIN_DENY'   => array(),

    /**
     * 1. 项目分组设定,多个组之间用逗号分隔,例如'Home,Admin'
     * 2. 分组模式 0 普通分组 1 独立分组
     * 3. 分组目录 独立分组模式下面有效
     *
     * TODO: 2、3项分析代码后删除
     */
    'APP_GROUP_LIST'        => 'Home,Admin,Api',
    'APP_GROUP_MODE'        =>  0,
    'APP_GROUP_PATH'        =>  'Modules',

    /**
     * 操作方法后缀
     * create => create_action
     */
    'ACTION_SUFFIX'         =>  '',

    // -------------------------------------------
    // 默认配置
    // -------------------------------------------
    /**
     * 1. 默认的模型层名称
     * 2. 默认的控制器层名称
     */
    'DEFAULT_M_LAYER'       => 'Model',
    'DEFAULT_C_LAYER'       => 'Controller',

    /**
     * 默认项目名称，@表示当前项目
     */
    /*'DEFAULT_APP'           => '@',*/

    /**
     * 默认语言
     */
    'DEFAULT_LANG'          => 'zh-cn',

    /**
     * 默认模板主题名称
     */
    /*'DEFAULT_THEME'         => '',*/

    /**
     * 1. 默认分组，当有Admin,Home,Api等分组时，默认为什么分组
     * 2. 默认模块名称，强制，不建议改变
     * 3. 默认操作名称，强制，不建议改变
     */
    'DEFAULT_GROUP'         => 'Home',
    'DEFAULT_MODULE'        => 'Index',
    'DEFAULT_ACTION'        => 'index',

    /**
     * 默认输出编码
     */
    'DEFAULT_CHARSET'       => 'utf-8',

    /**
     * 默认时区
     */
    'DEFAULT_TIMEZONE'      => 'PRC',

    /**
     * 1. 默认AJAX 数据返回格式,可选JSON XML ...
     * 2. 默认JSONP格式返回的处理方法
     */
    'DEFAULT_AJAX_RETURN'   => 'JSON',
    'DEFAULT_JSONP_HANDLER' => 'jsonpReturn',

    /**
     * 默认参数过滤方法
     * 用于 $this->_get('变量名');$this->_post('变量名')...
     */
    'DEFAULT_FILTER'        => 'htmlspecialchars',

    // -------------------------------------------
    // 数据库配置
    // -------------------------------------------
    /**
     * 1. 数据库类型，可以使用其他数据库类型，并配合驱动扩展使用
     * 2. 服务器地址
     */
    'DB_TYPE'               => 'mysql',
    'DB_HOST'               => 'localhost',

    /**
     * 1. 数据库名
     * 2. 用户名
     * 3. 密码
     */
    'DB_NAME'               => '',
    'DB_USER'               => 'root',
    'DB_PWD'                => '',

    /**
     * 1. 端口
     * 2. 数据库表前缀
     * 3. 数据库编码默认采用utf8
     */
    'DB_PORT'               => '',
    'DB_PREFIX'             => 'think_',
    'DB_CHARSET'            => 'utf8',

    /**
     * 是否进行字段类型检查
     * TODO: 当前没有地方用到
     */
    'DB_FIELDTYPE_CHECK'    => false,

    /**
     * 启用字段缓存
     */
    'DB_FIELDS_CACHE'       => true,

    /**
     * 1. 数据库部署方式:0 集中式(单一服务器), 1 分布式(主从服务器)
     * 2. 数据库读写是否分离 主从式有效
     * 3. 读写分离后 主服务器数量
     * 4. 指定从服务器序号
     */
    'DB_DEPLOY_TYPE'        => 0,
    'DB_RW_SEPARATE'        => false,
    'DB_MASTER_NUM'         => 1,
    'DB_SLAVE_NO'           => '',

    /**
     * 1. 数据库查询的SQL创建缓存
     * 2. SQL缓存队列的缓存方式 支持 file xcache和apc
     * 3. SQL缓存的队列长度
     */
    'DB_SQL_BUILD_CACHE'    => false,
    'DB_SQL_BUILD_QUEUE'    => 'file',
    'DB_SQL_BUILD_LENGTH'   => 20,

    /**
     * SQL执行日志记录
     */
    'DB_SQL_LOG'            => false,

    // -------------------------------------------
    // 数据缓存设置
    // -------------------------------------------
    'DATA_CACHE_TIME'       => 0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   => false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      => false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     => '',     // 缓存前缀
    'DATA_CACHE_TYPE'       => 'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       => TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     => false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       => 1,        // 子目录缓存级别

    /* 错误设置 */
    'ERROR_MESSAGE'         => '页面错误！请稍后再试～',//错误显示信息,非调试模式有效
    'ERROR_PAGE'            => '',	// 错误定向页面
    'SHOW_ERROR_MSG'        => false,    // 显示错误信息
    'TRACE_EXCEPTION'       => false,   // TRACE错误信息是否抛异常 针对trace方法 

    /* 日志设置 */
    'LOG_RECORD'            => false,   // 默认不记录日志
    'LOG_TYPE'              => 3, // 日志记录类型 0 系统 1 邮件 3 文件 4 SAPI 默认为文件方式
    'LOG_DEST'              => '', // 日志记录目标
    'LOG_EXTRA'             => '', // 日志记录额外信息
    'LOG_LEVEL'             => 'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_FILE_SIZE'         => 2097152,	// 日志文件大小限制
    'LOG_EXCEPTION_RECORD'  => false,    // 是否记录异常信息日志

    /* SESSION设置 */
    'SESSION_AUTO_START'    => true,    // 是否自动开启Session
    'SESSION_OPTIONS'       => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'          => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'        => '', // session 前缀
    //'VAR_SESSION_ID'      => 'session_id',     //sessionID的提交变量

    /* 模板引擎设置 */
    'TMPL_CONTENT_TYPE'     => 'text/html', // 默认模板输出类型
    'TMPL_TRACE_FILE'       => FRAME_PATH.'Tpl/page_trace.html',    // 调用trace页面
    // 'TMPL_ACTION_ERROR'     => FRAME_PATH.'Tpl/dispatch_jump.html', // 默认错误跳转对应的模板文件
    // 'TMPL_ACTION_SUCCESS'   => FRAME_PATH.'Tpl/dispatch_jump.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   => FRAME_PATH.'Tpl/exception.html',// 异常页面的模板文件
    'TMPL_DETECT_THEME'     => false,       // 自动侦测模板主题
    'TMPL_TEMPLATE_SUFFIX'  => '.html',     // 默认模板文件后缀
    'TMPL_FILE_DEPR'        =>  '/', //模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符

    /* URL设置 */
    'URL_CASE_INSENSITIVE'  => true,   // 默认false 表示URL区分大小写 true则表示不区分大小写

    'URL_PATHINFO_DEPR'     => '/',	// PATHINFO模式下，各参数之间的分割符号
    'URL_PATHINFO_FETCH'    =>   'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
    'URL_HTML_SUFFIX'       => '',  // URL伪静态后缀设置
    'URL_PARAMS_BIND'       =>  true, // URL变量绑定到Action方法参数
    'URL_404_REDIRECT'      =>  '', // 404 跳转页面 部署模式有效

    /* 系统变量名称设置 */
    'VAR_GROUP'             => 'g',     // 默认分组获取变量
    'VAR_MODULE'            => 'm',		// 默认模块获取变量
    'VAR_ACTION'            => 'a',		// 默认操作获取变量
    'VAR_AJAX_SUBMIT'       => 'ajax',  // 默认的AJAX提交变量
	'VAR_JSONP_HANDLER'     => 'callback',
    'VAR_PATHINFO'          => 's',	// PATHINFO 兼容模式获取变量例如 ?s=/module/action/id/1 后面的参数取决于URL_PATHINFO_DEPR
    'VAR_URL_PARAMS'        => '_URL_', // PATHINFO URL参数变量
    'VAR_TEMPLATE'          => 't',		// 默认模板切换变量
    'VAR_FILTERS'           => 'filter_exp',     // 全局系统变量的默认过滤方法 多个用逗号分割

    'OUTPUT_ENCODE'         =>  true, // 页面压缩输出
    'HTTP_CACHE_CONTROL'    =>  'private', // 网页缓存控制

    /* URL 跳转设置 */
    'JUMP_SESSION_ON'       => true,
    'JUMP_SESSION_INFO'     => 'jump_session_info',
    'JUMP_SESSION_STATUS'   => 'jump_session_status'

);