<?php

namespace Ucenter\Mobile\Controllers;

class captchaController extends ControllerBase
{
    public function initialize()
    {
        $this->captcha = $this->init('\Ucenter\Webpc\Controllers\CaptchaController');
    }

    public function getCodeAction()
    {
        $this->captcha->getCodeAction();
    }
}