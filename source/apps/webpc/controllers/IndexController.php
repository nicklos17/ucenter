<?php

namespace Ucenter\Webpc\Controllers;

use  Ucenter\Mdu\UserModule as Users,
        Ucenter\Mdu\ServiceModule as Service,
        Ucenter\Mdu\CaptchaModule as Captcha,
        Ucenter\Utils\WbApi,
        Ucenter\Utils\QcApi,
        Ucenter\Utils\cas\CAServer,
        Ucenter\Utils\Inputs;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->response->redirect('login');
    }

    public function loginAction()
    {
        $this->view->setVar('pageTitle', '登录');
        if ($this->request->isPost() == true)
        {
            if (!$this->validFlag)
            {
                echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
                $this->view->disable();
                return;
            }
            else
            {
                $user = new Users();
                $res = $user->login($this->_sanReq['mobile'], $this->_sanReq['passwd']);
                if($res == 1)
                {
                    $casTime = 0;
                    if($this->_sanReq['autoLogin'])
                    {
                        $time = $_SERVER['REQUEST_TIME'];
                        if($user->updateUserLoginTime($this->_sanReq['mobile'], $time))
                        {
                            $val = base64_encode(substr(md5($this->_sanReq['mobile']), 8, 20).':'.base64_encode($this->session->get('uid')).':'.base64_encode($time).':'.($time + 14* 86400));
                            setcookie(substr(md5($this->di['sysconfig']['siteUrl']), 5, 15), $val, $time + 14* 86400, '/');
                            $casTime = $time + 14 * 86400;
                        }
                    }

                    // cas  start
                    $cas = new CAServer();
                    if($cas->casSave($this->_sanReq['siteId'], $casTime))
                    {
                        // 登录成功，保存用户资料至redis
                        $cas->setRedisUserInfo();
                        
                        $backurl = $this->_sanReq['backurl'];
                        $backurl .= !empty($this->_sanReq['auth']) ? '?ticket=' . $st . '&backurl=' . urlencode('http://' . ltrim($backurl, 'http://')) : '';
                        echo json_encode(array('ret' => 1, 'backurl' => $backurl));
                    }
                    else
                    {
                        $this->session->destroy();
                        $key=substr(md5($this->di['sysconfig']['siteUrl']), 5, 15);
                        setcookie($key, '', $_SERVER['REQUEST_TIME']-3600, '/');
                        return;
                    }
                }
                elseif ($res == 10004)
                {
                    echo json_encode(array('ret' => 0,'msg'=> array('mobile' => array('msg' => $this->di['sysconfig']['flagMsg']['10004']))));
                }
                elseif($res == 10010)
                {
                    echo json_encode(array('ret' => 0,'msg'=> array('passwd' => array('msg' => $this->di['sysconfig']['flagMsg']['10010']))));
                }
                exit;
            }
        }
        else
        {
            $viewVars = array();
            // cas  start
            if ($this->request->getQuery('siteid'))
            {
                $siteId = $this->request->getQuery('siteid');
                $backurl = $this->request->getQuery('backurl');

                $cas = new CAServer();
                $tgc = $cas->getCookieTGC();
                if ($tgc)
                {
                    // 存在tgc，重新生成st并存入redis
                    $st = $cas->getST($siteId);
                    $tgt = $tgc['tgt'];
                    $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
                    $redis = $RedisLib::getRedis();
                    $resRedis = $redis->setex($st, 86400, $tgt);
                    if ($resRedis && $backurl)
                    {
                        // 跳转
                        $urlParse = parse_url($backurl);
                        $com = isset($urlParse['query']) ? '&' : '?';
                        if (isset($urlParse['query']))
                        {
                            $fragment = isset($urlParse['fragment']) ? '#' . $urlParse['fragment'] : '';
                            $baseUrl = $urlParse['scheme'] . '://' . $urlParse['host'] . ':' . $urlParse['port'] . $urlParse['path'] . '?' . urlencode($urlParse['query'] . $fragment);
                            $com = '&';
                        }
                        else
                        {
                            $baseUrl = $backurl;
                            $com = '?';
                        }
                        $backurl = $baseUrl . $com . 'ticket=' . $st . '&backurl=' . urlencode($backurl);
                        $this->response->redirect($backurl);
                        return;
                    }
                }
                else
                {
                    // 处理存在uid，但cas tgc不存在
                    if($this->session->get('uid'))
                        $cas->casSave($siteId);
                }
                $viewVars['siteId'] = $siteId;
            }
            // cas end
    
            $viewVars['backurl'] = $this->request->getQuery('backurl') ?: '';
            $viewVars['auth'] = $this->request->getQuery('auth') ?: '';

            if(empty($this->session->get('uid')))
            {
                $key = substr(md5($this->di['sysconfig']['siteUrl']), 5, 15);
                if ($this->cookies->has($key))
                {
                    $val = explode(':', base64_decode($this->cookies->get($key)));
                    $uid = base64_decode($val[1]);
                    $lastTime = base64_decode($val[2]);
                    $keepTime = $val[3];
                    $nowTime = $_SERVER['REQUEST_TIME'];
                    $user = new Users();
                    $userInfo = $user->getUserInfo($uid);
                    if($lastTime == $userInfo['u_last_logintime'] && $val[0] === substr(md5($userInfo['u_mobi']), 8, 20))
                    {
                        if($user->updateUserLoginTime($userInfo['u_mobi'], $nowTime))
                        {
                            $val = base64_encode(substr(md5($userInfo['u_mobi']), 8, 20).':'.base64_encode($uid).':'.base64_encode($nowTime).':'.$keepTime);
                            setcookie(substr(md5($this->di['sysconfig']['siteUrl']), 5, 15), $val, $keepTime, '/');
                        }

                        $this->session->set('uid', $userInfo['u_id']);
                        $this->session->set('uinfo', array('name' => $userInfo['u_name'], 'mobile' => $userInfo['u_mobi']));
                        $this->response->redirect('user/index');
                    }
                }
            }
            else
                $this->response->redirect('user/index');

            $this->view->setVars($viewVars);
        }
    }

    public function registerAction()
    {
        $this->view->setVar('pageTitle', '注册');
        if ($this->request->isPost() == true)
        {
            if (!$this->validFlag)
            {
                echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
                exit;
            }
            else
            {
                $user=new Users();
                if($mobiRes = $user->existTel($this->_sanReq['mobile']))
                {
                    if($mobiRes == 1)
                    {
                        echo json_encode(array('ret' => 0,'msg'=> array('mobile' => array('msg' => $this->di['sysconfig']['flagMsg']['10002']))));
                        $this->view->disable();
                        return;
                    }
                }
                $res = $user->isEmailExist($this->_sanReq['email']);
                if($res['u_email'])
                {
                    echo json_encode(array('ret' => 0,'msg'=> array('email' => array('msg' => $this->di['sysconfig']['flagMsg']['10015']))));
                    $this->view->disable();
                    return;
                }

                $objCaptcha = new Captcha();
                $key = 'reg:'.$this->_sanReq['mobile'];
                $data = $objCaptcha->checkAllCaptcha($this->_sanReq['mobile'], $this->_sanReq['codeImg'],$key, $this->_sanReq['regtype'], $this->_sanReq['captcha']);
                if($data == 1)
                {
                    $res=$user->register($this->_sanReq['passwd'],$this->_sanReq['mobile'], $this->_sanReq['email']);
                    if($res == 1)
                    {
                        $objCaptcha->delCaptchaRedisKey($key);
                        echo json_encode(array('ret' => 1));
                    }
                    elseif($res ==10002)
                    {
                        echo json_encode(array('ret' => 0,'msg'=> array('mobile' => array('msg' => $this->di['sysconfig']['flagMsg']['10002']))));
                    }
                    else
                    {
                        echo json_encode(array('ret' => 0,'msg'=> array('service' => array('msg' => $this->di['sysconfig']['flagMsg']['10000']))));
                    }

                    $this->view->disable();
                    return;
                }
                else
                {
                    echo json_encode(array('ret' => 0,'msg'=>$data));
                    $this->view->disable();
                    return;
                }
            }
        }
    }

    public function addUserInfoAction()
    {
        if ($this->request->isPost() == true)
        {
            if (!$this->validFlag)
            {
                echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
                $this->view->disable();
                return;
            }
            else
            {
                $user=new Users();
                $objCaptcha = new Captcha();
                $key = 'oauth:'.$this->_sanReq['mobile'];
                $data = $objCaptcha->checkAllCaptcha($this->_sanReq['mobile'], $this->_sanReq['codeImg'],$key, $this->_sanReq['regtype'], $this->_sanReq['captcha']);
                if($data == 1)
                {

                    switch ($this->_sanReq['thirdType']) {
                        case 'qq':
                            $res=$user->qqRegister($this->_sanReq['passwd'], $this->_sanReq['mobile'], $this->_sanReq['email'], $pic = $this->_sanReq['pic'], $this->_sanReq['thirdOpenid']);
                            break;
                        case 'wb':
                            $res=$user->wbRegister($this->_sanReq['passwd'], $this->_sanReq['mobile'], $this->_sanReq['email'], $pic = $this->_sanReq['pic'], $this->_sanReq['thirdOpenid']);
                            break;
                        case 'wx':
                            $res=$user->wxRegister($this->_sanReq['passwd'], $this->_sanReq['mobile'], $this->_sanReq['email'], $pic = $this->_sanReq['pic'], $this->_sanReq['thirdOpenid']);
                            break;
                        case 'alipay':
                            $res=$user->alipayRegister($this->_sanReq['passwd'], $this->_sanReq['mobile'], $this->_sanReq['email'], $pic = $this->_sanReq['pic'], $this->_sanReq['thirdOpenid']);
                            break;
                        default:
                            $res = 0;
                            break;
                    }
                    if($res == 1)
                    {
                        $objCaptcha->delCaptchaRedisKey($key);
                        echo json_encode(array('ret' => 1));
                    }
                    elseif($res ==10002)
                    {
                        echo json_encode(array('ret' => 0,'msg'=> array('mobile' => array('msg' => $this->di['sysconfig']['flagMsg']['10002']))));
                    }
                    else
                    {
                        echo json_encode(array('ret' => 0,'msg'=> array('service' => array('msg' => $this->di['sysconfig']['flagMsg']['10000']))));
                    }

                    $this->view->disable();
                    return;
                }
                else
                {
                    echo json_encode(array('ret' => 0,'msg'=>$data));
                    $this->view->disable();
                    return;
                }
            }
        }
        else
        {
            $tmpInfo=$this->cookies->get(substr(md5('tmpQqWb'), 8, 20));
            $info =json_decode(base64_decode($tmpInfo->getValue()));
            switch ($info->type) {
                case 'qq':
                    $typeMsg = 'QQ';
                    break;
                case 'wb':
                    $typeMsg = '微博';
                    break;
                case 'wx':
                    $typeMsg = '微信';
                    break;
                case 'alipay':
                    $typeMsg = '支付宝';
                    break;
                default:
                    $typeMsg = '';
                    break;
            }

            $this->view->setVar('third_type', $info->type);
            $this->view->setVar('third_openid', $info->uids);
            $this->view->setVar('pageTitle', "您已成功使用$typeMsg 账号登录到云朵，请先完善您的资料");
            $this->view->setVar('name', $info->name);
            $this->view->setVar('pic', $info->pic);
            $this->view->pick('index/adduserinfo');
        }

    }
    /**
     * [existTelAction ajax 判断手机号是否已被注册]
     * @return　integer  [１存在，０不存在]
     */
    public function existTelAction()
    {
        if (!$this->validFlag)
        {
            echo json_encode(array('ret' =>0, 'msg' => $this->warnMsg));
            exit;
        }
        else
        {
            $mobile=$this->_sanReq['mobile'];
            $user=new Users();
            $res = $user->existTel($mobile);
            if($res == 1)
            {
                echo json_encode(array('ret' => 1));
            }
            else
            {
                echo json_encode(array('ret' => 0,'msg'=> array('service' => array('msg' => $this->di['sysconfig']['flagMsg']['10004']))));
            }

            $this->view->disable();
            return;
        }
    }

    /**
     * [qqOauthAction QQ认证登陆]
     */
    public function qqOauthAction()
    {
        $qc = new QcApi( $this->di );
        $qc->loginUrl();
    }

    /**
     * [wbOauthAction 微薄认证登陆]
     */
    public function wbOauthAction()
    {
        $wb = new WbApi($this->di);
        $wb->getAuthorizeUrl();
    }

    public function weixinAction()
    {
        $weixin = new \Ucenter\Utils\WeixinApi($this->di);
        $this->response->redirect($weixin->loginUrl());
    }

    public function alipayAction()
    {
        $alipay = new \Ucenter\Utils\AlipayApi($this->di);
        $this->response->redirect($alipay->loginUrl());
    }

    //注册成功调转页面
    public function registerSuccessAction()
    {
        $this->view->setVar('pageTitle', '您已成功注册云朵账户');
    }

    public function logoutAction()
    {
        // cas   logout
        $cas = new CAServer();
        if($cas->logout())
        {
            $cas->delRedisUserInfo();
            $this->session->destroy();
            $key=substr(md5($this->di['sysconfig']['siteUrl']), 5, 15);
            setcookie($key, '', $_SERVER['REQUEST_TIME']-3600, '/');
            $this->response->redirect('login');
        }
        else
        {
            echo "<script>alert('退出失败，请重新尝试');window.history.back();</script>";
        }
        $this->view->disable();
    }

    private function _autoLogin()
    {
        if(empty($this->session->get('uid')))
        {
            $key = substr(md5($this->di['sysconfig']['siteUrl']), 5, 15);
            if ($this->cookies->has($key))
            {
                $val = explode(':', base64_decode($this->cookies->get($key)));
                $uid = base64_decode($val[1]);
                $time = base64_decode($val[2]);
                $user = new Users();
                $userInfo = $user->getUserInfo($uid);
                if($time==$userInfo['u_last_logintime'])
                {
                    if($val[0] === substr(md5($userInfo['u_mobi']), 8, 20))
                    {
                        $this->session->set('uid', $userInfo['u_id']);
                        $this->session->set('uinfo', array('name' => $userInfo['u_name'], 'mobile' => $userInfo['u_mobi']));
                        $this->response->redirect('user/index');
                    }
                }
            }
        }
        else
            $this->response->redirect('user/index');
    }

    public function validateAction()
    {
        $param = $this->request->getQuery();
        $status = "no\n";
        $info = '';
        if ($st = $param['ticket'])
        {
            // 获取redis中tgt
            $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
            $redis = $RedisLib::getRedis();
            if ($tgt = $redis->get($st))
            {
                $cas = new CAServer();
                $status = "yes\n";
                $info = $cas->encrypt($cas->getSiteIdByST($st), json_encode($tgt));
            }
        }
        echo $status;
        echo $info;
        $this->view->disable();
    }

    public function sessidAction()
    {
        $st = $this->request->getQuery('st');
        $backurl = $this->request->getQuery('backurl');

        $siteArr = explode('-', $st);
        $siteId = $siteArr[1];

        setcookie($siteId, $st);
        $this->response->redirect($backurl);
    }
}
