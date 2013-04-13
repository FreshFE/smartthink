<?php
namespace Think;

class Tag {

	public static function listen($tag, &$params = null) {

	    // 系统标签扩展
	    $extends = C('extends.' . $tag);
	    
	    // 项目标签扩展
	    $tags = C('tags.' . $tag);


	    if(!empty($tags)) {
	    	// 合并扩展
	        if(empty($tags['_overlay']) && !empty($extends)) {
	            $tags = array_unique(array_merge($extends, $tags));
	        }
	        // 通过设置 '_overlay'=>1 覆盖系统标签
	        elseif(isset($tags['_overlay'])) {
	            unset($tags['_overlay']);
	        }
	    }
	    elseif(!empty($extends)) {
	        $tags = $extends;
	    }

	    // 如果存在该tag则执行
	    if($tags) {

	        if(APP_DEBUG) {
	            Debug::mark($tag.'Start');
	            Debug::trace('[ '.$tag.' ] --START--','','INFO');
	        }
	        // 执行扩展
	        foreach ($tags as $key=>$name) {
	        	
	        	// 指定行为类的完整路径 用于模式扩展
	            if(!is_int($key)) {
	                $name = $key;
	            }
	            // 执行
	            static::run($name, $params);
	        }
	        // 记录行为的执行日志
	        if(APP_DEBUG) {
	            Debug::trace('[ '.$tag.' ] --END-- [ RunTime:'.Debug::mark($tag.'Start',$tag.'End',6).'s ]','','INFO');
	        }
	    }
	    // 未执行任何行为 返回false
	    else {
	        return false;
	    }
	}

	/**
	 * 执行某个行为
	 * @param string $name 行为名称
	 * @param Mixed $params 传人的参数
	 * @return void
	 */
	public static function run($name, &$params = null) {

	    $class = __NAMESPACE__ . '\\Behaviors\\' . $name;

	    // 载入
	    // Import::loads(array(
     //        CORE_PATH . 'Behavior/' . $class . EXT,
     //        LIB_PATH . 'Behavior/' . $class . EXT,
     //        GROUP_PATH . 'Behavior/' . $class . EXT
     //    ));

	    if(APP_DEBUG) {
	        Debug::mark('behaviorStart');
	    }

	    // 实例化并执行
	    $behavior = new $class();
	    $behavior->run($params);

	    // 记录行为的执行日志
	    if(APP_DEBUG) {
	        Debug::mark('behaviorEnd');
	        Debug::trace('Run '.$name.' Behavior [ RunTime:'.Debug::mark('behaviorStart','behaviorEnd',6).'s ]','','INFO');
	    }
	}
}