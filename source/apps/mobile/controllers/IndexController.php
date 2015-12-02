<?php

namespace Ucenter\Mobile\Controllers;

use  Ucenter\Mdu\UserModule as Users,
Ucenter\Mdu\ServiceModule as Service,
Ucenter\Mdu\CaptchaModule as Captcha,
Ucenter\Utils\WbApi,
Ucenter\Utils\QcApi;

class IndexController extends ControllerBase
{
    public function initialize()
    {
        $this->index=$this->init('\Ucenter\Webpc\Controllers\IndexController');
    }

    public function indexAction()
    {
      if(empty($this->session->get('uid')))
        {
            $this->response->redirect('login');
        }
        else 
        {
            $this->response->redirect('user/index');
        }   
    }

    public function loginAction()
    {
        $this->index->loginAction();
    }

    public function registerAction()
    {
        $this->index->registerAction();
    }

    public function addUserInfoAction()
    {
        $this->index->addUserInfoAction();
    }
    
    public function existTelAction()
    {
        $this->index->existTelAction();
    }

    /**
     * [qqOauthAction QQ认证登陆]
     */
    public function qqOauthAction()
    {
        $qc=new QcApi($this->di);
        $qc->loginUrl();
    }

    /**
     * [wbOauthAction 微薄认证登陆]
     */
    public function wbOauthAction()
    {
        $wb=new WbApi($this->di);
        $wb->getAuthorizeUrl();
    }

    public function alipayAction()
    {
        $alipay = new \Ucenter\Utils\AlipayApi($this->di);
        $this->response->redirect($alipay->loginUrl());
    }

    //注册成功调转页面
    public function registerSuccessAction()
    {
        $this->index->registerSuccessAction();
    }

    public function logoutAction()
    {
        $this->index->logoutAction();
    }

    public function validateAction()
    {
        $this->index->validateAction();
    }

    public function sessidAction()
    {
        $this->index->sessidAction();
    }
}