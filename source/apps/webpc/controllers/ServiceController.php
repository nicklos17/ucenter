<?php

namespace Ucenter\Webpc\Controllers;

use  Ucenter\Mdu\ServiceModule as Service;
use  Ucenter\Mdu\UserModule as User;


class ServiceController extends ControllerBase
{

    /**
     * [sendMsgAction 发送短信 发送验证码60秒内只能发送一次]
     * @return [type] [error 0 success 1]
     */
    public function sendMsgAction()
    {

        if (!$this->validFlag)
        {
             echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            $Service=new Service($this->di);
       
            if(isset($this->_sanReq['mobile']))
            {
                $mobile = $this->_sanReq['mobile'];
            }
            else
             {
                    $mobile = $this->session->get('uinfo')['mobile'];
            }
            $res = $Service->makeMsg($this->_sanReq['type'], $mobile);
            if($res)
            {
                echo json_encode(array('ret' => 1));
            }
            else
            {
                echo json_encode(array('ret' => 0, 'msg' => array('sendMsg' => array('msg' => $this->di['sysconfig']['flagMsg']['10018']))));
            }
        }
        exit();
    }

    /**
     * [sendMailAction 发送邮件]
     * @return [type] [description]
     */
    public function sendMailAction()
    {
        if (!$this->validFlag)
        {
             echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            if(isset($this->_sanReq['mobile']))
            {
                $email = $this->_sanReq['email'];
            }
            else
            {
                $user = new User();
                $userinfo = $user->getUserInfo($this->session->get('uid'));
                $email = $userinfo['u_email'];
            }
            $Service=new Service( $this->di );
            $Service->sendMails( $this->session->get('uid'), $email);
            echo json_encode(array('ret' => 1));
        }
        exit();
    }
}
