<?php

class Tag {

	public static function mark($tag, &$params=NULL) {
	    // 系统标签扩展
	    $extends    = C('extends.' . $tag);
	    // 应用标签扩展
	    $tags       = C('tags.' . $tag);
	    if (!empty($tags)) {
	        if(empty($tags['_overlay']) && !empty($extends)) { // 合并扩展
	            $tags = array_unique(array_merge($extends,$tags));
	        }elseif(isset($tags['_overlay'])){ // 通过设置 '_overlay'=>1 覆盖系统标签
	            unset($tags['_overlay']);
	        }
	    }elseif(!empty($extends)) {
	        $tags = $extends;
	    }
	    if($tags) {
	        if(APP_DEBUG) {
	            G($tag.'Start');
	            Debug::trace('[ '.$tag.' ] --START--','','INFO');
	        }
	        // 执行扩展
	        foreach ($tags as $key=>$name) {
	            if(!is_int($key)) { // 指定行为类的完整路径 用于模式扩展
	                $name   = $key;
	            }
	            static::run($name, $params);
	        }
	        if(APP_DEBUG) { // 记录行为的执行日志
	            Debug::trace('[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]','','INFO');
	        }
	    }else{ // 未执行任何行为 返回false
	        return false;
	    }
	}

	/**
	 * 执行某个行为
	 * @param string $name 行为名称
	 * @param Mixed $params 传人的参数
	 * @return void
	 */
	public static function run($name, &$params=NULL) {
	    $class      = $name.'Behavior';
	    if(APP_DEBUG) {
	        G('behaviorStart');
	    }
	    $behavior   = new $class();
	    $behavior->run($params);
	    if(APP_DEBUG) { // 记录行为的执行日志
	        G('behaviorEnd');
	        Debug::trace('Run '.$name.' Behavior [ RunTime:'.G('behaviorStart','behaviorEnd',6).'s ]','','INFO');
	    }
	}
}