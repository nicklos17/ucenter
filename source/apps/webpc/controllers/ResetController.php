<?php

namespace Ucenter\Webpc\Controllers;
use Ucenter\Mdu\CaptchaModule as Captcha;
use  Ucenter\Mdu\UserModule as User;

class ResetController extends ControllerBase
{
    //忘记密码页面
    public function indexAction()
    {
        $this->view->setVar('pageTitle', '验证手机号');
    }

    //通过短信验证
    public function passAction()
    {
        $this->view->setVar('pageTitle', '设置新密码');
        if(empty($this->session->get('resetToken')))
        {
            $this->response->redirect('reset/index');
        }
    }

    //设置成功页面
    public function successAction()
    {
        $this->view->setVar('pageTitle', '修改成功');
    }

    /**
     * [checkcapAction description]
     * @return [type] [1 成功，10004 手机号未注册]
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
            $key = 'resetPwd:'.$this->_sanReq['mobile'];
            $data = $objCaptcha->checkAllCaptcha($this->_sanReq['mobile'], $this->_sanReq['codeImg'],$key, $this->_sanReq['regtype'], $this->_sanReq['captcha']);
            if($data == 1)
            {
                $user = new User();
                $res = $user->existTel($this->_sanReq['mobile']);
                if(!$res)
                {
                    echo json_encode(array('ret' => 0,'msg'=> array('email' => array('msg' => $this->di['sysconfig']['flagMsg']['10004']))));
                }   
                $token = \Ucenter\Utils\Inputs::makeSecert($this->_sanReq['mobile'],$_SERVER['REQUEST_TIME']);
                $this->session->set('resetToken',$token);
                $this->session->set('resetMobile',$this->_sanReq['mobile']);
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
    //从置密码
    public function setPwdAction()
    {
        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            $user = new User();
            $data = $user->getUid($this->session->get('resetMobile'));
            $res = $user->changePwd($data['u_id'],$this->_sanReq['passwd']);
            if($res)
            {
                $this->session->remove('resetToken');
                $this->session->remove('resetMobile');
                echo json_encode(array('ret' => 1));
            }
        }
        exit();
    }
}


