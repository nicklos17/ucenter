<?php

namespace Ucenter\Mobile\Controllers;

use Ucenter\Utils\QcApi;
use Ucenter\Utils\WbApi;
use \Phalcon\Acl\Exception as E;
use  Ucenter\Mdu\UserModule as Users;

class OauthController extends ControllerBase
{

    public function initialize()
    {
        $this->oauth = $this->init('\Ucenter\Webpc\Controllers\OauthController');
    }

    public function qqCallBackAction()
    {
        $this->oauth->qqCallBackAction();
    }

    public function sinaCallbackAction()
    {
        $this->oauth->sinaCallBackAction();
    }

    public function wxCallbackAction()
    {
        $this->oauth->wxCallbackAction();
    }

    public function alipayCallbackAction()
    {
        $this->oauth->alipayCallbackAction();
    }
}