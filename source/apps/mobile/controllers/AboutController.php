<?php

namespace Ucenter\Mobile\Controllers;

class AboutController extends ControllerBase
{

    public function initialize()
    {
        $this->about = $this->init('\Ucenter\Webpc\Controllers\AboutController');
    }

    /**
     * 网站使用条款
     */
    public function clauseAction()
    {
        $this->about->clauseAction();
    }

    /**
     * 隐私条款
     */
    public function privacyAction()
    {
        $this->about->privacyAction();
    }
}