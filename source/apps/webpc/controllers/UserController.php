<?php

namespace Ucenter\Webpc\Controllers;

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
            $this->response->redirect("login");
        }
    }

    public function indexAction()
    {
        $this->view->setVar('pageTitle', '个人中心');
        $users = new Users();
        $userinfo = $users->getUserInfo($this->session->get('uid'));
        $this->view->setVars(array(
            'userinfo' => $userinfo,
        ));
    }

    /**
     * [accountBindAction 获取用户绑定的第三方信息]
     * @return [type] [description]
     */
    public function accountBindAction()
    {
        $this->view->setVar('pageTitle', '账号绑定');
        $users = new Users();
        if($this->session->has('uid'))
        {
            $userinfo = $users->getUserInfo($this->session->get('uid'));
            $this->view->setVars(array(
                'userinfo' => $userinfo,
            ));
        }
    }

    /**
     * [accountUnBindAction 用户绑定的第三方信息]
     * @return [type] [description]
     */
    public function accountUnBindAction()
    {
        if (!$this->validFlag)
        {
             echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
            $this->view->disable();
            return;
        }
        else
        {
            $users = new Users();
            if($this->session->has('uid'))
            {
                if($users->unBingQqWb($this->session->get('uid'), $this->_sanReq['type']))
                    echo json_encode(array('ret' => 1));
                else
                    echo json_encode(array('ret' => 0,'msg'=> array('service' => array('msg' => $this->di['sysconfig']['flagMsg']['10000']))));
                $this->view->disable();
                return;
            }
        }
    }

    public function passwdEditAction()
    {
        $this->view->setVar('pageTitle', '修改密码');
    }

    public function addUserInfoAction()
    {
        $tmpInfo=$this->cookies->get(substr(md5('tmpQqWb'), 8, 20));
        $info =json_decode(base64_decode($tmpInfo->getValue()));
        $this->view->setVar("name", $info->name);
        $this->view->setVar("wbuid", $info->uids);
        $this->view->setVar("pic", $info->profile_image_url);
    }

    public function editNameAction()
    {
        if (!$this->validFlag)
        {
             echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            $users = new Users();
            if($users->isNickExist($this->_sanReq['name']) == 1)
            {
                echo json_encode(array('ret' => 0,'msg'=> array('name' => array('msg' => $this->di['sysconfig']['flagMsg']['10014']))));
                $this->view->disable();
                return;
            }
            $users->changeNickname($this->session->get('uid'),$this->_sanReq['name']);
            $this->session->set('uinfo',array('name' => $this->_sanReq['name'], 'mobile'=>$this->session->get('uinfo')['mobile']));

            // 更新redis用户信息
            $cas = new \Ucenter\Utils\cas\CAServer();
            $cas->setRedisUserInfo($this->session->get('uid'), array('name' => $this->_sanReq['name'], 'mobile'=>$this->session->get('uinfo')['mobile']));

            echo json_encode(array('ret' => 1, 'nickName' => $this->_sanReq['name']));
        }
            $this->view->disable();
            return;
    }

    /**
     * [uploadPhotoAction 上传用户头像]
     * @return [type] [description]
     */
    public function uploadPhotoAction()
    {
        if($_FILES['upfile']['error'] == 0)
        {
            $upload = new ImgUpload($this->di, 300, 300);
            list($width, $height) = getimagesize($_FILES['upfile']['tmp_name']);
            if($width < 100 || $height < 100)
            {
                echo json_encode(['ret' => 0, 'msg' => '图片长宽都不能小于100px']);
                $this->view->disable();
                return;
            }
            try {
                $pic = $upload->upload_file($_FILES['upfile']);
            } catch (Exception $e) {
                echo json_encode(['ret' => 0, 'msg' => $e.getMessage()]);
                $this->view->disable();
            }
            //抛出报错情况
            if($upload->errmsg)
            {
                echo json_encode(['ret' => 0, 'msg' => $upload->errmsg]);
            }
            else
            {
                $config = $this->di->get('sysconfig');
                //获取压缩后图片高宽
                list($w, $h) = getimagesize($config['avatarAccess']. '/'. $pic);
                $picUrl = $config['staticServer'].$config['avatarAccess']. '/'. $pic;
                $this->di['session'] ->set('userAvatar', [__DIR__.'/../../../'.$this->di['sysconfig']['avatar'] . '/' . $pic, $picUrl]);
                echo json_encode(['ret' => 1, 'msg' => $picUrl, 'w' => $w, 'h' => $h]);
            }
            $this->view->disable();
            return;
        } else {
            echo json_encode(['ret' => 0, 'msg' => '服务器上传文件失败']);
            $this->view->disable();
        }
    }

    /**
     * [cropPhoto 裁剪用户头像]
     * @return [type] [description]
     */
    public function cropPhotoAction()
    {
        if(!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
            $this->view->disable();
            return;
        }
        else
        {
            if($this->di['session']->has('userAvatar'))
            {
                $img = $this->di['session'] ->get('userAvatar');
                if(ImgUpload::resizeThumbnailImage(preg_replace('/\.jpg/', '_thumb.jpg', $img[0]), $img[0], $this->_sanReq['w'], $this->_sanReq['h'],
                $this->_sanReq['x1'], $this->_sanReq['y1'], 100/$this->_sanReq['w']))
                {
                    unlink($img[0]);
                    $users = new Users();
                    $users ->changeAvatar($this->session->get('uid'), preg_replace('/\.jpg/', '_thumb.jpg', $img[1]));
                    unset ($_SESSION['userAvatar']);
                }
            }
            $this->response->redirect('user/index');
        }
    }

    public function editEmailAction()
    {

        if (!$this->validFlag)
        {
             echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            $users = new Users();
            $res = $users->isEmailExist($this->_sanReq['email']);
            if($res['u_email'])
            {
                echo json_encode(array('ret' => 0,'msg'=> array('email' => array('msg' => $this->di['sysconfig']['flagMsg']['10015']))));
                $this->view->disable();
                return;
            }
            $users->setEmail($this->session->get('uid'),$this->_sanReq['email']);
            $Service = new Service();
            $Service->sendMails( $this->session->get('uid'),$this->_sanReq['email']);
            echo json_encode(array('ret' => 1));
        }
        $this->view->disable();
        return;
    }

    /**
     * Captcha 1验证码有效 2验证码失效 3验证码错误 4需要图像验证码 5图像验证码错误
     */
    public function editPasswdAction()
    {
        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
        }
        else
        {
            $objCaptcha = new Captcha();
            $key = 'editPwd:'.$this->session->get('uinfo')['mobile'];
            $data = $objCaptcha->checkAllCaptcha($this->session->get('uinfo')['mobile'], $this->_sanReq['codeImg'],$key, $this->_sanReq['regtype'], $this->_sanReq['captcha']);
            if($data == 1)
            {
                $users = new Users();
                $users->changePwd($this->session->get('uid'), $this->_sanReq['passwd']);
                $objCaptcha->delCaptchaRedisKey($key);
                echo json_encode(array('ret' => 1));
            }
            else
            {
                echo json_encode(array('ret' => 0,'msg'=>$data));
            }
        }
        $this->view->disable();
        return;
    }

    public function deviceAction()
    {
        $this->view->setVar('pageTitle', '我的设备');
        $Service = new Service($this->di);
        $devicesList = json_decode($Service->getDevices($this->session->get('uid')), true);
        //$devicesList = json_decode($Service->getDevices(1), true);
        $this->view->setVar('devices', $devicesList);
    }

    public function changeAvatarAction()
    {
        $this->view->setVar('pageTitle', '修改头像');
    }

}
