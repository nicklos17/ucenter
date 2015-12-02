<?php

namespace Ucenter\Mobile\Controllers;

use  Ucenter\Mdu\ServiceModule as Service;
use  Ucenter\Mdu\UserModule as User;


class ServiceController extends ControllerBase
{
    public function initialize()
    {
        $this->service=$this->init('\Ucenter\Webpc\Controllers\ServiceController');
    }
    //发送短信 发送验证码60秒内只能发送一次 error 0 success 1
    public function sendMsgAction()
    {
        $this->service->sendMsgAction();
    }

    //发送邮件
    public function sendMailAction()
    {
        $this->service->sendMailAction();
    }
}
