<?php

namespace Ucenter\Utils;
use \Redis ;
class RedisLib
{

	private static $obj = NULL;
	protected $di;

	public function __construct($di)
	{
		$this->di=$di;
		$redisConf=$this->di->get( 'sysconfig' )['redis'] ;
		self::$obj = new Redis();
		self::$obj->connect($redisConf['server'], $redisConf['port'], $redisConf['timeout']);
		self::$obj->select($redisConf['dbname']);
		self::$obj->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);  
	}

	/**
	 * 获取redis对象
	 * @return Redis
	 */
	public static function getRedis()
	{
		if(!self::$obj)
		{
			new RedisLib();
		}
		return self::$obj;
	}
	/**
	 * 自增和自减特殊操作，防止执行失败
	 */
	public static function autoOption($key, $method)
	{
		$redis = self::getRedis();
		$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
		if($method == 'incr')
		{
			return $redis->incr($key);
		}
		elseif($method == 'decr')
		{
			return $redis->decr($key);
		}
	} 
	
}