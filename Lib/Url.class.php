<?php
/**
 * Core/Url.class.php
 * MeSmart php
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/MeSmart
 * @package       Core.Url
 * @since         MeSmart php 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * Url Class
 * 生成url地址
 */
class Url {

	/**
	 * 解析地址
	 * 生成新地址
	 *
	 * @param string $url
	 * @param array|string $vars
	 * @param string $suffix
	 *
	 * @return string
	 */
	public static function make($url, $vars, $suffix)
	{
		// 解析
		$urls = parse_url($url);

		// 解析path
		$_path = static::parse_path($urls);

		// 解析query
		$_query = static::parse_query($urls, $vars);

		// 解析锚点
		$_flag = static::parse_flag($urls);

		// 解析前缀
		$_prefix = static::parse_prefix($prefix);

		// 解析后缀
		$_suffix = static::parse_suffix($suffix);

		// 合成地址
		$_url = $_prefix . $_path . $_query . $_suffix . $_flag;

		// 返回美化后的地址
		return static::beauty($_url);
	}

	/**
	 * 解析path部分
	 *
	 * @param array $urls
	 *
	 * @return string
	 */
	private static function parse_path($urls)
	{
		// 不存在scheme，则分析补全
		if(!$urls['scheme'])
		{
			$path = static::auto_path($urls['path']);
		}
		// 存在scheme，则返回完整的
		else {
			Debug::throw_exception('No absoulte url support, please use string.');
		}

		return $path;
	}

	/**
	 * 解析pathinfo部分
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private static function auto_path(string $url)
	{
		// 根目录
		if($url == '/') {
			$urls = array(GROUP_NAME, CONTROLLER_NAME, ACTION_NAME);
		}
		else {
			// 解析
			$urls = explode('/', $url);

			// 只有一项时，认为是action name
			if(count($urls) == 1) {
				array_unshift($urls, GROUP_NAME, CONTROLLER_NAME);
			}
			// 只有两项时，认为是controll name和action name
			else if(count($urls) == 2) {
				array_unshift($urls, GROUP_NAME);
			}
		}

		// 删除默认分组项
		if($urls[0] === Config::get('DEFAULT_GROUP')) {
			unset($urls[0]);
		}

		return join($urls, '/');
	}

	/**
	 * 解析锚点
	 *
	 * @param $urls
	 *
	 * @return string
	 */
	private static function parse_flag($urls)
	{
		return $urls['fragment'] ? '#' . $urls['fragment'] : '';
	}

	/**
	 * 解析query请求部分
	 *
	 * @param string $url
	 * @param mixed $vars
	 *
	 * @return string
	 */
	private static function parse_query(array $urls, mixed $vars)
	{
		$temp = '';

		// 存在第二参数的形式
		if($vars)
		{
			if(is_string($vars)) {
				$temp =  '/' . trim($vars, '/');
			}

			if(is_array($vars))
			{
				foreach ($vars as $key => $value) {
					$temp .= '/' . $key . '/' . $value;
				}
			}
		}

		// 写入$url字符串的形式
		if($urls['query'])
		{
			$querys = explode('&', $urls['query']);

			foreach ($querys as $key => $value) {
				$temp .= '/' . str_replace('=', '/', $value);
			}
		}

		return $temp;
	}

	/**
	 * 解析后缀
	 *
	 * @param string $url
	 * @param mixed $suffix
	 *
	 * @return string
	 */
	private static function parse_suffix($suffix)
	{
		// 等于true则获取配置选项
		if($suffix === true)
		{
			return Config::get('URL_HTML_SUFFIX');
		}
		// 不等于true，但又存在不为false则赋值
		else if($suffix)
		{
			return $suffix;
		}
		// 默认为空
		else {
			return '';
		}
	}

	/**
	 * 解析前缀
	 * 根据是否设置了rewrite来区分前缀为'index.php/'还是'/'
	 *
	 * @param string $url
	 * @param mixed $prefix
	 *
	 * @return string
	 */
	private static function parse_prefix(string $url, string $prefix)
	{
		return Config::get('URL_REWRITE') ? '/' : (_PHP_FILE_ . '/');
	}

	/**
	 * 美化url
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private static function beauty(string $url)
	{

		$url = strtolower($url);

		while (!$stop) {
			// 删除最后位的index
			if(substr(rtrim($url, '/'), -5) === 'index'){
				$url = substr(rtrim($url, '/'), 0, -5);
			}
			else {
				$stop = true;
			}
		}

		return $url;
	}
}