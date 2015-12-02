<?php

namespace Ucenter\Webpc\Controllers;

use Ucenter\Mdu\CaptchaModule as Captcha;
use  Ucenter\Mdu\UserModule as User;
use  Ucenter\Mdu\ServiceModule as Service;

class EmailController extends ControllerBase
{
    public function initialize()
    {
        if(empty($this->session->get('uid')))
        {
            $this->response->redirect('login');
        }
    }
    /**
     * [indexAction 修改邮箱页面]
     * @return [type] [description]
     */
    public function indexAction()
    {
        $this->view->setVar('pageTitle', '更改邮箱');
        $user = new User();
        $userinfo = $user->getUserInfo($this->session->get('uid'));
        if($userinfo['u_email_verify'] != '3')
        {
            $this->response->redirect('user/index');
        }
    }

    /**
     * [changeAction 输入新邮箱]
     * @return [type] [description]
     */
    public function changeAction()
    {
        $this->view->setVar('pageTitle', '更改邮箱');
        if(empty($this->session->get('emailToken')))
        {
            $this->response->redirect('email/index');
        }
        $user = new User();
        $userinfo = $user->getUserInfo($this->session->get('uid'));
        $this->view->setVars(array(
            'userinfo' => $userinfo,
        ));
    }

    /**
     * [successAction 设置成功页面]
     * @return [type] [description]
     */
    public function successAction()
    {
        $this->view->setVar('pageTitle', '更改绑定邮箱成功');
        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
        }
        if (empty($this->session->get('uinfo')['name']) && $this->session->get('uinfo')['name'] != '0')
        {
            $uname = substr_replace($this->session->get('uinfo')['mobile'], '****', 4, -3);
        }
        else
        {
            $uname = $this->session->get('uinfo')['name'];
        }
        $this->view->setVars(array(
            'uname' => $uname,
            'email' => $this->_sanReq['email'],
        ));
    }

    /**
     * 设一个隐藏标签regtype = 11
     * [checkcapAction 检查手机和验证码]
     * @return [type] [1验证码有效，]
     */
    public function checkcapAction()
    {

        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            $objCaptcha = new Captcha();
            $key = 'email:'.$this->session->get('uinfo')['mobile'];
            $data = $objCaptcha->checkAllCaptcha($this->session->get('uinfo')['mobile'], $this->_sanReq['codeImg'],$key, $this->_sanReq['regtype'], $this->_sanReq['captcha']);
            if($data == 1)
            {
                $token = \Ucenter\Utils\Inputs::makeSecert($this->session->get('uinfo')['mobile'],$_SERVER['REQUEST_TIME']);
                $this->session->set('emailToken',$token);
                $objCaptcha->delCaptchaRedisKey($key);
                echo json_encode(array('ret' => 1));
            }
            else
            {
                echo json_encode(array('ret' => 0,'msg'=>$data));
            }
        }
        exit();
    }

    /**
     * 验证邮箱是否存在
     * @return string [1 邮箱存在]
     */
    public function checkEmailAction()
    {
        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else 
        {
            $user = new User();
            $res = $user->isEmailExist($this->_sanReq['email']);
            if($res['u_email'])
            {
                echo json_encode(array('ret' => 1));
            }
        }
        exit();
    }

    /**
     * [resetEmailAction 重置邮箱]
     * @return [type] [1 修改成功，10015 该邮件已存在，10031 非法操作]
     */
    public function resetEmailAction()
    {
        if(empty($this->session->get('emailToken')))
        {
            echo json_encode(array('ret' => 0,'msg'=> array('email' => array('msg' => $this->di['sysconfig']['flagMsg']['10031']))));
        }

        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else 
        {   
            $user = new User();
            $res = $user->isEmailExist($this->_sanReq['email']);
            if($res['u_email'])
            {
                echo json_encode(array('ret' => 0,'msg'=> array('email' => array('msg' => $this->di['sysconfig']['flagMsg']['10015']))));
            }
            else
            {
                $res = $user->resetEmail($this->session->get('uid'), $this->_sanReq['email']);
                if($res)
                {
                    $Service=new Service($this->di);
                    $Service->sendMails($this->session->get('uid'), $this->_sanReq['email']);
                    $this->session->remove('emailToken');
                    echo json_encode(array('ret' => 1));
                }
            }
        }
        exit();
    }
}


