<?php

namespace Ucenter\Webpc\Controllers;

use Ucenter\Utils\QcApi;
use Ucenter\Utils\WbApi;
use \Phalcon\Acl\Exception as E;
use  Ucenter\Mdu\UserModule as Users;

class OauthController extends ControllerBase
{
    public function indexAction()
    {
    
    }

    public function qqCallBackAction()
    {
        try
        {
            $config=$this->di->get('sdkconfig');
            $appid=$config['QQ']['appid'];
            //获取accesstoken 
            $qc=new QcApi($this->di);
            $accessToken = $qc->getAccessToken();
            //获取openid
            $openId=$qc->getOpenid();
            $keysArr = [
                'oauth_consumer_key' => $appid,
                'access_token' => $accessToken,
                'openid' => $openId,
                'format' =>'json'
            ];
            if($this->session->has('uid')&&!empty($openId))
            {
                $user=new Users();
                if($user->getUinfo($openId, 'qq')) 
                {
                    echo '<script>alert("绑定失败，该账号已绑定其他用户");window.location.href="/user/accountbind";</script>';
                    return;
                }
                else
                {
                    if($user->bingQq($this->session->get('uid'),$openId))
                        $this->response->redirect('user/accountbind');
                    else
                        throw new E('QQ bing failed');
                }
            }
            else
            {
                $user=new Users();
                if($uinfo = $user->getUinfo($openId, 'qq'))
                {
                    $cas = new \Ucenter\Utils\cas\CAServer();
                    $this->session->set('uid', $uinfo['u_id']);
                    $this->session->set('uinfo', array('name' => $uinfo['u_name'], 'mobile' => $uinfo['u_mobi']));
                    $cas->casSave(0, 0);
                    $this->response->redirect('user/index');
                }
                else
                {
                    $url='https://graph.qq.com/user/get_user_info';
                    $userInfo=$qc->get($url, $keysArr);
                    $userInfo=json_decode($userInfo);
                    $info = array('type'=>'qq', 'name'=>$userInfo->nickname, 'uids'=>$openId, 'pic'=>$userInfo->figureurl_qq_2);
                    setcookie(substr(md5('tmpQqWb'), 8, 20), base64_encode(json_encode($info)), $_SERVER['REQUEST_TIME']+1800, '/');
                    $this->response->redirect('index/addUserInfo');
                }
                
            }
        }
        catch( E $e) 
        {
            $this->response->redirect('login');
        }
    }

    public function sinaCallbackAction()
    {
        try
        {
            $wb=new WbApi($this->di);
            $accessToken = $wb->getAccessToken();
            //获取的uid
            $wb_uid = $wb->getUid();
            $uids=$wb_uid['uid'];
            if($this->session->has('uid')&&!empty($uids))
            {
                $user=new Users();
                if($user->getUinfo($uids, 'wb')) 
                {
                    echo '<script>alert("绑定失败，该账号已绑定其他用户");window.location.href="/user/accountbind";</script>';
                    return;
                }
                else
                {
                    if($user->bingWb($this->session->get('uid'), $uids))
                        $this->response->redirect('user/accountbind');
                    else
                        throw new E('WB bing failed');
                }
            }
            else
            {
                $user=new Users();
                 if($uinfo=$user->getUinfo($uids, 'wb'))
                {
                    $cas = new \Ucenter\Utils\cas\CAServer();
                    $this->session->set('uid', $uinfo['u_id']);
                    $this->session->set('uinfo', array('name' => $uinfo['u_name'], 'mobile' => $uinfo['u_mobi']));
                    $cas->casSave(0, 0);
                    $this->response->redirect('user/index');
                }
                else
                {
                    //获取用户信息
                    $uidInfo = $wb->getUserInfoByUid( $uids );
                    $info = array('type'=>'wb', 'name'=>$uidInfo['name'], 'uids'=>$uids, 'pic'=>$uidInfo['profile_image_url']);
                    setcookie(substr(md5('tmpQqWb'), 8, 20), base64_encode(json_encode($info)), $_SERVER['REQUEST_TIME']+1800, '/');
                    $this->response->redirect('index/addUserInfo');
                }
            }
        }
        catch(E $e)
        {
            $this->response->redirect('login');
        }
    }

    public function wxCallbackAction()
    {
        $weixin = new \Ucenter\Utils\WeixinApi($this->di);
        $acInfo = json_decode($weixin->getAccessToken($_REQUEST['code'], $_REQUEST['state']), true);

        if (isset($acInfo['errcode']))
            header('Location: /');

        $openId = $acInfo['openid'];
        $accessToken = $acInfo['access_token'];
        if($this->session->has('uid')&&isset($openId))
        {
            $user=new Users();
            if($user->getUinfo($openId, 'wx')) 
            {
                echo '<script>alert("绑定失败，该账号已绑定其他用户");window.location.href="/user/accountbind";</script>';
                return;
            }
            else
            {
                if($user->bingWX($this->session->get('uid'), $openId))
                    $this->response->redirect('user/accountbind');
                else
                    throw new E('WX bing failed');
            }
        }
        else
        {
            $user=new Users();
            if($uinfo=$user->getUinfo($openId, 'wx'))
            {
                $cas = new \Ucenter\Utils\cas\CAServer();
                $this->session->set('uid', $uinfo['u_id']);
                $this->session->set('uinfo', array('name' => $uinfo['u_name'], 'mobile' => $uinfo['u_mobi']));
                $cas->casSave(0, 0);
                $this->response->redirect('user/index');
            }
            else
            {
                //获取用户信息
                $uidInfo = json_decode($weixin->getUserInfo($accessToken, $openId), true);
                if (!isset($uidInfo['openid'])) 
                    header('Location: /');

                $info = array('type'=>'wx', 'name'=>$uidInfo['nickname'], 'uids'=>$openId, 'pic'=>$uidInfo['headimgurl']);
                setcookie(substr(md5('tmpQqWb'), 8, 20), base64_encode(json_encode($info)), $_SERVER['REQUEST_TIME']+1800, '/');
                $this->response->redirect('index/addUserInfo');
            }
        }
    }

    public function alipayCallbackAction()
    {
        $alipay = new \Ucenter\Utils\AlipayApi($this->di);
        $acInfo = $alipay->verifyReturn();

        if (!$acInfo)
            header('Location: /');

        $openId = $_GET['user_id'];
        if($this->session->has('uid')&&isset($openId))
        {
            $user=new Users();
            // 校验该第三方账号是否已存在
            if($user->getUinfo($openId, 'alipay')) 
            {
                echo '<script>alert("绑定失败，该账号已绑定其他用户");window.location.href="/user/accountbind";</script>';
                return;
            }
            else
            {
                if($user->bindAlipay($this->session->get('uid'), $openId))
                    $this->response->redirect('user/accountbind');
                else
                    throw new E('Ailpay bind failed');
            }
        }
        else
        {
            $user=new Users();
            if($uinfo=$user->getUinfo($openId, 'alipay'))
            {
                $cas = new \Ucenter\Utils\cas\CAServer();
                $this->session->set('uid', $uinfo['u_id']);
                $this->session->set('uinfo', array('name' => $uinfo['u_name'], 'mobile' => $uinfo['u_mobi']));
                $cas->casSave(0, 0);
                $this->response->redirect('user/index');
            }
            else
            {
                //获取用户信息
                $uidInfo = $_GET;
                if (!isset($uidInfo['user_id'])) 
                    header('Location: /');

                $info = array('type'=>'alipay', 'name'=>$uidInfo['real_name'], 'uids'=>$openId, 'pic'=>'');
                setcookie(substr(md5('tmpQqWb'), 8, 20), base64_encode(json_encode($info)), $_SERVER['REQUEST_TIME']+1800, '/');
                $this->response->redirect('index/addUserInfo');
            }
        }
    }
}