<?php
/**
 * PHP SDK for  OpenAPI V3
 *
 * @version 3.0.9
 * @author open.qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 * @ History:
 */

require_once 'lib/SnsNetwork.php';
require_once 'lib/SnsSigCheck.php';
require_once 'lib/SnsStat.php';
 
/**
 * 如果您的 PHP 没有安装 cURL 扩展，请先安装 
 */
if (!function_exists('curl_init'))
{
	throw new Exception('OpenAPI needs the cURL PHP extension.');
}

/**
 * 如果您的 PHP 不支持JSON，请升级到 PHP 5.2.x 以上版本
 */
if (!function_exists('json_decode'))
{
	throw new Exception('OpenAPI needs the JSON PHP extension.');
}

/**
 * 错误码定义
 */
define('OPENAPI_ERROR_REQUIRED_PARAMETER_EMPTY', 1801); // 参数为空
define('OPENAPI_ERROR_REQUIRED_PARAMETER_INVALID', 1802); // 参数格式错误
define('OPENAPI_ERROR_RESPONSE_DATA_INVALID', 1803); // 返回包格式错误
define('OPENAPI_ERROR_CURL', 1900); // 网络错误, 偏移量1900, 详见 http://curl.haxx.se/libcurl/c/libcurl-errors.html

/**
 * 提供访问腾讯开放平台 OpenApiV3 的接口
 */
class OpenApiV3
{
	private $appid  = 0;
	private $appkey = '';
	private $server_name = '';
	private $format = 'json';
	private $stat_url = "apistat.tencentyun.com";
	private $is_stat = true;
	
	/**
	 * 构造函数
	 *
	 * @param int $appid 应用的ID
	 * @param string $appkey 应用的密钥
	 */
	function __construct($appid, $appkey)
	{
		$this->appid = $appid;
		$this->appkey = $appkey;
	}
	
	public function setServerName($server_name)
	{
		$this->server_name = $server_name;
	}
	
	public function setStatUrl($stat_url)
	{
		$this->stat_url = $stat_url;
	}
	
	public function setIsStat($is_stat)
	{
		$this->is_stat = $is_stat;
	}
	
	/**
	 * 执行API调用，返回结果数组
	 *
	 * @param string $script_name 调用的API方法，比如/v3/user/get_info，参考 http://wiki.open.qq.com/wiki/API_V3.0%E6%96%87%E6%A1%A3
	 * @param array $params 调用API时带的参数
	 * @param string $method 请求方法 post / get
	 * @param string $protocol 协议类型 http / https
	 * @return array 结果数组
	 */
	public function api($script_name, $params, $method='post', $protocol='http')
	{
		// 检查 openid 是否为空
		if (!isset($params['openid']) || empty($params['openid']))
		{
			return array(
				'ret' => OPENAPI_ERROR_REQUIRED_PARAMETER_EMPTY,
				'msg' => 'openid is empty');
		}
		// 检查 openid 是否合法
		if (!self::isOpenId($params['openid']))
		{
			return array(
				'ret' => OPENAPI_ERROR_REQUIRED_PARAMETER_INVALID,
				'msg' => 'openid is invalid');
		}
		
		// 添加一些参数
		$params['appid'] = $this->appid;
		$params['format'] = $this->format;

		$url = $protocol . '://' . $this->server_name . $script_name;
		$cookie = array();
        
		//记录接口调用开始时间
		$start_time = SnsStat::getTime();
		
		//通过调用以下方法，可以打印出最终发送到openapi服务器的请求参数以及url，默认为注释
		//self::printRequest($url,$params,$method);
		
		
		// 发起请求
		$ret = SnsNetwork::makeRequest($url, $params, $cookie, $method, $protocol);
		
		if (false === $ret['result'])
		{
			$result_array = array(
				'ret' => OPENAPI_ERROR_CURL + $ret['errno'],
				'msg' => $ret['msg'],
			);
		}
		
		$result_array = json_decode($ret['msg'], true);
		
		// 远程返回的不是 json 格式, 说明返回包有问题
		if (is_null($result_array)) {
			$result_array = array(
				'ret' => OPENAPI_ERROR_RESPONSE_DATA_INVALID,
				'msg' => $ret['msg']
			);
		}

		// 统计上报
		if ($this->is_stat)
		{
			$stat_params = array(
					'appid' => $this->appid,
					'svr_name' => $this->server_name,
					'interface' => $script_name,
					'protocol' => $protocol,
					'method' => $method,
			);
			SnsStat::statReport($this->stat_url, $start_time, $stat_params);
		}
		
		//通过调用以下方法，可以打印出调用openapi请求的返回码以及错误信息，默认注释
		//self::printRespond($result_array);
		
		return $result_array;
	}

	/**
	 * 打印出请求串的内容，当API中的这个函数的注释放开将会被调用。
	 *
	 * @param string $url 请求串内容
	 * @param array $params 请求串的参数，必须是array
	 * @param string $method 请求的方法 get / post
	 */
	private function printRequest($url, $params,$method)
	{
		$query_string = SnsNetwork::makeQueryString($params);
		if($method == 'get')
		{
			$url = $url."?".$query_string;
		}
		echo "\n============= request info ================\n\n";
		print_r("method : ".$method."\n");
		print_r("url    : ".$url."\n");
		if($method == 'post')
		{
			print_r("query_string : ".$query_string."\n");
		}
		echo "\n";
		print_r("params : ".print_r($params, true)."\n");
		echo "\n";
	}
	
	/**
	 * 打印出返回结果的内容，当API中的这个函数的注释放开将会被调用。
	 *
	 * @param array $array 待打印的array
	 */
	private function printRespond($array)
	{
		echo "\n============= respond info ================\n\n";
		print_r($array);
		echo "\n";
	}
	
	/**
	 * 检查 openid 的格式
	 *
	 * @param string $openid openid
	 * @return bool (true|false)
	 */
	private static function isOpenId($openid)
	{
		return (0 == preg_match('/^[0-9a-fA-F]{32}$/', $openid)) ? false : true;
	}
}