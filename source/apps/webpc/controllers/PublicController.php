<?php

namespace Ucenter\Webpc\Controllers;
class publicController extends ControllerBase
{
    /**
     * [errorAction 错误页面]
     * @return [type] [description]
     */
     public function errorAction()
    {

    }
    
    /**
     * [emailErrAction 邮件错误]
     * @return [type] [description]
     */
    public function emailErrAction()
    {
    
    }

    public function error404Action()
    {
       $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT);
    }
}