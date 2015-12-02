<?php

namespace Ucenter\Mobile\Controllers;
use Ucenter\Mdu\CaptchaModule as Captcha;
use  Ucenter\Mdu\UserModule as User;

class ResetController extends ControllerBase
{
    public function initialize()
    {
        $this->reset=$this->init('\Ucenter\Webpc\Controllers\ResetController');
    }
    //忘记密码页面
    public function indexAction()
    {
    
    }

    //通过短信验证
    public function passAction()
    {
        if(empty($this->session->get('resetToken')))
        {
            $this->response->redirect('reset/index');
        }
    }

    //设置成功页面
    public function successAction()
    {
    
    }
    /**
     * 检查手机和验证码
     * 设一个隐藏标签regtype = 7
     */
    public function checkcapAction()
    {
        $this->reset->checkcapAction();
    }
    //从置密码
    public function setPwdAction()
    {
        $this->reset->setPwdAction();
    }
  
}