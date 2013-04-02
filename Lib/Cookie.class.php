<?php namespace meSmart;

class Cookie {

    static protected $config = array(
        'prefix'    =>  '',     // 名称前缀
        'expire'    =>  0,      // 保存时间
        'path'      =>  '/',    // 保存路径
        'domain'    =>  '',     // 有效域名
    );

    /**
     * Cookie初始化
     *
     * @param array $config
     *
     * @return void
     */
    static public function init($config = array()) {
        static::$config = array_merge(static::$config, array_change_key_case($config));
    }

    /**
     * 设置或者获取cookie作用域（前缀）
     *
     * @param string $prefix
     *
     * @return string|void
     */
    static public function prefix($prefix = '') {
        if(empty($prefix)) {
            return static::$config['prefix'];
        }else{
            static::$config['prefix'] = $prefix;
        }
    }

    /**
     * Cookie 设置、获取、删除
     *
     * @param string $name cookie名称
     * @param mixed $value cookie值
     * @param mixed $options cookie参数
     *
     * @return mixed
     */
    static public function set($name, $value = '', $option = null) {

        // 参数设置(会覆盖黙认设置)
        if(!is_null($option)) {

            if(is_numeric($option)) {
                $option = array('expire' => $option);
            }
            elseif(is_string($option)) {
                parse_str($option, $option);
            }

            $config = array_merge(static::$config, array_change_key_case($option));
        }
        // 默认配置参数
        else{
            $config = static::$config;
        }

        // 合并前缀
        $name = $config['prefix'] . $name;

        // 设置cookie
        if(is_array($value)){
            $value  = 'think:'.json_encode(array_map('urlencode',$value));
        }

        // 计算时间
        $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;

        // cookie配置
        setcookie($name, $value, $expire, $config['path'], $config['domain']);

        // 保存cookie
        $_COOKIE[$name] = $value;
    }

    /**
     * Cookie获取
     *
     * @param string $name cookie名称
     * @param string $prefix cookie前缀
     *
     * @return mixed
     */
    static public function get($name, $prefix='') {

        // 前缀，合成$name
        $prefix =   $prefix?$prefix:static::$config['prefix'];
        $name = $prefix . $name;

        // 是否存在cookie
        if(isset($_COOKIE[$name])) {

            $value = $_COOKIE[$name];

            if(0===strpos($value,'think:')) {
                $value  =   substr($value,6);
                return array_map('urldecode',json_decode($value,true));
            }
            else {
                return $value;
            }
        }
        else {
            return null;
        }
    }

    /**
     * Cookie删除
     *
     * @param string $name cookie名称
     * @param string $prefix cookie前缀
     *
     * @return mixed
     */
    static public function delete($name, $prefix = '') {

        // 合成前缀
        $prefix = $prefix?$prefix:static::$config['prefix'];
        $name = $prefix . $name;

        // 删除指定cookie
        setcookie($name, '', time() - 3600, static::$config['path'], static::$config['domain']);
        unset($_COOKIE[$name]);
    }

    /**
     * Cookie清空
     *
     * @param string $prefix cookie前缀
     *
     * @return mixed
     */
    static public function clear($prefix = '') {

        // 清除指定前缀的所有cookie
        if(empty($_COOKIE)) {
            return;
        }
            
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = $prefix ? $prefix: static::$config['prefix'];

        // 如果前缀为空字符串将不作处理直接返回
        if ($prefix) {
            foreach ($_COOKIE as $key => $val) {
                if (0 === strpos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, static::$config['path'], static::$config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        else{
            unset($_COOKIE);
        }
        
        return;
    }
}