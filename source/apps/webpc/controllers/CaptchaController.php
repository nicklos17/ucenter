<?php

namespace Ucenter\Webpc\Controllers;

use Ucenter\Utils\CaptchaImage;

class CaptchaController extends ControllerBase
{
    /**
     * [getCodeAction 验证码图片]
     * @return [type] [description]
     */
    public function getCodeAction()
    {
        $code = new CaptchaImage();
        $code->doimg();
        $this->session->set('code',$code->getCode());
        exit();
    }
}