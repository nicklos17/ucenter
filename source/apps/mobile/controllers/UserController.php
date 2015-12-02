<?php

namespace Ucenter\Mobile\Controllers;
use  Ucenter\Mdu\UserModule as Users,
Ucenter\Mdu\CaptchaModule as Captcha,
Ucenter\Utils\ImgUpload,
Ucenter\Mdu\ServiceModule as Service;

class UserController extends ControllerBase
{
    public function initialize()
    {
        if(empty($this->session->get('uid')))
        {
            $this->response->redirect('login');
        }
        $this->user=$this->init('\Ucenter\Webpc\Controllers\UserController');
    }

    public function indexAction()
    {
        $this->user->indexAction();
    }

    public  function passwdEditAction()
    {
        $this->user->passwdEditAction();
    }

    public function editPasswdAction()
    {
        $this->user->editPasswdAction();
    }

    public function editSuccessAction()
    {

    }

    public function editNameAction()
    {
        $this->user->editNameAction();
    }

    public function uploadPhotoAction()
    {
        $this->user->uploadPhotoAction();
    }

    public function cropPhotoAction()
    {
        $this->user->cropPhotoAction();
    }
}