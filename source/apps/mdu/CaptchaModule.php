<?php

namespace Ucenter\Mdu;
use Ucenter\Mdu\Models\CaptchaModel;
class CaptchaModule extends ModuleBase
{
    protected $objCaptcha;

    const SUCCESS = 1;
    const ERROR = 10000;
    const CAPERR = 10011;
    const CAP_UN_VALID = 10012;
    public function __construct()
    {
        $this->objCaptcha = $this->initModel('\Ucenter\Mdu\Models\CaptchaModel');
    }

    /**
     * 获取最近一条验证码的信息
     * @param unknown $mobi
     * @param unknown $type
     */
    public function captchaInfoByMobi($mobi,$type)
    {
        return $this->objCaptcha->captchaInfoByMobi($mobi, $type);
    }

    /**
     * 根据验证码获取生成时间和验证时间
     * @param unknown $mobi
     * @param unknown $type
     * @param unknown $captcha
     */
    public function getCapthaTime($mobi, $type, $captcha)
    {
        return $this->objCaptcha->getCapthaTime($mobi, $type, $captcha);
    }

    /**
     * 完成验证码的验证
     * @param unknown $mobi
     * @param unknown $captcha
     * @param unknown $type
     * @param unknown $validtime
     */
    public function valid($mobi, $captcha, $type, $validtime)
    {
        return $this->objCaptcha->updateCaptcha($mobi, $captcha, $type, $validtime);
    }

    /**
     * 验证码校验
     * @param unknown $mobi
     * @param unknown $type
     * @param unknown $captcha
     * @param unknown $nowtime
     */
    public function checkCaptcha($mobi,$type, $captcha, $nowtime)
    {
        $capInfo = $this->objCaptcha->getCapthaTime($mobi,$type,$captcha);
        if(empty($capInfo))
        {
            return self::CAPERR;//验证码错误
        }
        else
        {
            //验证码超过失效
            if($nowtime - $capInfo['mc_addtime'] > $this->di['sysconfig']['capValid'] || $capInfo['mc_validtime'])
            {
            return self::CAP_UN_VALID;//验证码失效
            }
            $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
            $redis = $RedisLib::getRedis();
            $redis->delete($mobi.$type);
            return self::SUCCESS; //验证码有效返回1
        }
    }
    /**
     *
     * @param unknown $codeImg
     * @param unknown $key
     */
    public function checkAllCaptcha($mobi, $codeImg, $key, $type, $captcha)
    {
        $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();
        $res = $this->checkCaptcha($mobi, $type, $captcha, $_SERVER['REQUEST_TIME']);
        if($res != '1')
        {
            if($redis->get($key) == FALSE)
            {
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
                $redis->setex($key,86400,1);
            }
            else
            {
                $RedisLib::autoOption($key,'incr');
            }
            return array('captcha'=> array('msg' => $this->di['sysconfig']['flagMsg'][$res]));
        }
        else 
        {
            if($redis->get($key) >= '3')
            {
                if(!empty($codeImg))
                {
                    if(strtolower($codeImg) != $this->di['session']->get('code'))
                    {
                        return array('codeImg' => array('msg' => $this->di['sysconfig']['flagMsg']['10011']));
                    }
                }
                else
                {
                    return array('codeImg' => array('msg' =>'10030'));
                } 

            }
            $this->valid($mobi, $captcha, $type, $_SERVER['REQUEST_TIME']);
            return self::SUCCESS;
          }
    }

    public function delCaptchaRedisKey($key)
    {
        $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();
        $redis->delete($key);
    }
}
