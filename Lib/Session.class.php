<?php
namespace Think;

class Session {

	public static function config($name) {

		// 如果设置了前缀，则替换conf
		if(isset($name['prefix'])) C('SESSION_PREFIX', $name['prefix']);

		// 根据默认值设置获得根据定义的id设置
		if(C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')]))
		    session_id($_REQUEST[C('VAR_SESSION_ID')]);

		elseif(isset($name['id']))
		    session_id($name['id']);

		// session 函数参考，http://www.php.net/manual/zh/ref.session.php
		// session runtime 配置参考，http://www.php.net/manual/zh/session.configuration.php
		ini_set('session.auto_start', 0);

		if(isset($name['name']))            session_name($name['name']);
		if(isset($name['path']))            session_save_path($name['path']);
		if(isset($name['domain']))          ini_set('session.cookie_domain', $name['domain']);
		if(isset($name['expire']))          ini_set('session.gc_maxlifetime', $name['expire']);
		if(isset($name['use_trans_sid']))   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
		if(isset($name['use_cookies']))     ini_set('session.use_cookies', $name['use_cookies']?1:0);
		if(isset($name['cache_limiter']))   session_cache_limiter($name['cache_limiter']);
		if(isset($name['cache_expire']))    session_cache_expire($name['cache_expire']);
		if(isset($name['type']))            C('SESSION_TYPE',$name['type']);

		// 如果存在其他session类型
		if(C('SESSION_TYPE')) {

		    // 读取session驱动
		    $class = 'Session'. ucwords(strtolower(C('SESSION_TYPE')));

		    // 检查驱动类是否存在并加载，不存在则抛出错误
		    if(Import::load(EXTEND_PATH.'Driver/Session/'.$class.'.class.php')) {

		        $hander = new $class();
		        $hander->execute();

		    }else {
		        // 类没有定义
		        Debug::throw_exception(L('_CLASS_NOT_EXIST_').': ' . $class);
		    }
		}

		// 启动session
		if(C('SESSION_AUTO_START'))  session_start();
	}

	public static function get($name) {
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
	}

	public static function set($name, $value) {

		if(is_null($value)) {
			unset($_SESSION[$name]);
		}
		else {
			$_SESSION[$name] = $value;
		}
	}

	public static function check($name) {

		return isset($_SESSION[$name]);
	}

	public static function clear() {
		$_SESSION = array();
	}

	public static function pause() {
		session_write_close();
	}

	public static function start() {
		session_start();
	}

	public static function destroy() {
		$_SESSION =  array();
		session_unset();
		session_destroy();
	}

	public static function regenerate() {
		session_regenerate_id();
	}
}