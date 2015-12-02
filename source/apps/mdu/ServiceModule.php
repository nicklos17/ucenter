<?php

namespace Ucenter\Mdu;

class ServiceModule extends ModuleBase
{
    protected $service,$captcha,$emailVerify,$users;

    public function __construct()
    {
        $this->service = $this->initModel('\Ucenter\Mdu\Models\ServiceModel');
        $this->captcha = $this->initModel('\Ucenter\Mdu\Models\CaptchaModel');
        $this->emailVerify = $this->initModel('\Ucenter\Mdu\Models\EmailVerifyModel');
        $this->users = $this->initModel('\Ucenter\Mdu\Models\UsersModel');
    }

    /**
     * 生成短信验证码
     * 判断用户获得验证码的资格:type:
     * 1 - 已经注册过的用户不能再次获取注册验证码
     * 7 - 只有注册过的用户才能获取忘记密码的验证码
     * 9 - 只有注册过的用户才能获取修改密码的验证码
     * 11 - 只有注册过的用户才能获取更换邮箱验证码
     */
    public function makeMsg($type,$mobi)
    {
        require_once dirname(dirname(__FILE__)).'/utils/rpcService.php';
        $lastCapthaInfo = $this->captcha->getLastCapthaInfo($mobi, $type);
        if($_SERVER['REQUEST_TIME'] - $lastCapthaInfo['mc_addtime'] < 60)
        {
            return false;
        }
        $res = $this->users->getUidByMobi($mobi);

        switch ($type)
        {
            case 1:
                if($res)
                   exit('90002');
                $content = $this->di['sysconfig']['regMsg'];
                break;
            case 7:
                if(empty($res))
                    exit('90001');
                $content = $this->di['sysconfig']['resetMsg'];
                break;
            case 9:
                if(empty($res))
                    exit('90001');
                $content = $this->di['sysconfig']['changeMsg'];
                break;
            case 11:
                if(empty($res))
                    exit('90001');
                $content = $this->di['sysconfig']['chaEmailMsg'];
                break;
        }

        //生成验证码
        $captcha = \Ucenter\Utils\Inputs::random(4);
        $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();
        if($redis->get($mobi.$type) == FALSE)
        {
            $redis->set($mobi.$type,$captcha,1800);
        }
        
        $captcha = $redis->get($mobi.$type);
        $data = $this->captcha->addCaptcha($mobi,$type,$_SERVER['REQUEST_TIME'],$captcha);//验证码入库
        if($data)
        {
            $sendMsg = new \rpcService\RpcService($this->di['sysconfig']['thrift']['ip'], $this->di['sysconfig']['thrift']['port']);
            $sendMsg->smsSend($mobi, sprintf($content, $captcha));
            return true; 
        }
        else
        {
            exit('90018');
        }
    }

    /*
     * 发送邮件
     * $subject 主题
     * $toMailAdr 发给谁
     * $content 邮件内容
     */
    public  function sendMails($uid,$toMailAdr)
     {
          $secert = \Ucenter\Utils\Encrypt::authcode($uid,'ENCODE');
          $res = $this->emailVerify->innserEmailVerify($uid,$toMailAdr,$secert);
          $emailTplPath = dirname(dirname(__FILE__)).'/webpc/views/Template/email.html';
          $emailTpl = file_get_contents($emailTplPath);
          $urlCode = $this->di['sysconfig']['emailValidurl'].'?token='.rawurlencode($secert);
          $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
          $redis = $RedisLib::getRedis();
          $redis->lPush('mail_verify', array('mail_address' => $toMailAdr, 'mail_verify_code' => $urlCode));

          return true;
     }

    public function getDevices($uid)
    {
        require_once dirname(dirname(__FILE__)).'/utils/rpcService.php';
        $rpcServer = new \rpcService\RpcService($this->di['sysconfig']['thrift']['ip'], $this->di['sysconfig']['thrift']['port']);
        return $rpcServer->getDevices($uid);
    }
     
}
