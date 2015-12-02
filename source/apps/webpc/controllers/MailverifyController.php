<?php

namespace Ucenter\Webpc\Controllers;

use  Ucenter\Mdu\UserModule as User;
use  Ucenter\Mdu\EmailVerifyModule as EmailVerify;

class MailverifyController extends ControllerBase
{
    /**
     * [validemailAction 邮件验证]
     * @return [type] [description]
     */
    public function validemailAction()
    {
        $emailVerify = new EmailVerify($this->di);
        if (!$this->validFlag)
        {
            echo $this->warnMsg;
        }
        else
        {
            if(empty($this->session->get('uid')))
            {
                $backurl = $this->di['sysconfig']['domain'] . '/mailverify/validemail?token='.$this->_sanReq['token'];
                $this->response->redirect($this->di['sysconfig']['domain'] . '/login?bakcurl=' . $backurl);
            }
            $uid = \Ucenter\Utils\Encrypt::authcode($this->_sanReq['token'], 'DECODE');
            // $user = new User();
            // $userinfo = $user->getUserInfo($uid);

            $res = $emailVerify->validemail($uid, $this->_sanReq['token'], $_SERVER['REQUEST_TIME']);
            $this->showMsg($this->di['sysconfig']['domain'] . '/user/index', $this->di['sysconfig']['emailMsg'][$res],'用户中心', $res);
        }
    }
}
