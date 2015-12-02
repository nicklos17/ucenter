<?php

namespace Ucenter\Webpc\Controllers;

class AboutController extends ControllerBase
{

    /**
     * 网站使用条款
     */
    public function clauseAction()
    {
        $this->view->setVar('pageTitle', '使用条款');
    }

    /**
     * 隐私条款
     */
    public function privacyAction()
    {
        $this->view->setVar('pageTitle', '隐私条款');
    }
}