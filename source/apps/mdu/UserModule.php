<?php

namespace Ucenter\Mdu;

use  Phalcon\Acl\Exception as E;
use Ucenter\Mdu\Models\UsersModel;
use Ucenter\Utils\cas\CAServer;

class UserModule extends ModuleBase
{
    protected $user;

    const SUCCESS = 1;
    const ERROR = 10000;
    const AUTH_ERROR = 0;
    const MOBI_EXITE = 10002;
    const MOBI_NON_EXITE = 10004;
    const PASS_ERR = 10010;
    public function __construct()
    {
        $this->user = $this->initModel('\Ucenter\Mdu\Models\UsersModel');
    }
    /**
     * [register 用户注册模块]
     * @param  string $email [用户填写的邮箱]
     * @param  string $passwd [用户填写的密码]
     * @param  string $mobile [用户填写的手机]
     * @param  string $qqOpenId [qq openid]
     * @param  string $wbUid [微薄uid]
     * @return integer [电话存在返回２，注册失败返回０，注册成功返回１]
     */
    public function register($passwd, $mobile, $email = '')
    {
        $user = $this->user->getUidByMobi($mobile);
        if($user['u_id'])
            return self::MOBI_EXITE;
        else
        {
            $regTime = $_SERVER['REQUEST_TIME'];
            $passwd = \Ucenter\Utils\Inputs::makeSecert($passwd, $regTime);
            if( $uid = $this->user->addUser( $email, $passwd, $mobile, $regTime, '', '', ''))
            {
                $cas = new CAServer();
                $this->di['session']->set('uid', $uid);
                $this->di['session']->set('uinfo', array('name' => '', 'mobile' => $mobile));
                $cas->casSave(0, 0);
                return self::SUCCESS;
            }
            else 
                return self::ERROR;
        }
        
    }

    public function qqRegister($passwd, $mobile, $email = '', $pic= '', $qqOpenId = '')
    {
        $user = $this->user->getUidByMobi($mobile);
        if($user['u_id'])
            return self::MOBI_EXITE;
        else
        {
            $regTime = $_SERVER['REQUEST_TIME'];
            $passwd = \Ucenter\Utils\Inputs::makeSecert($passwd, $regTime);
            if( $uid = $this->user->addUser( $email, $passwd, $mobile, $regTime,$pic, $qqOpenId, ''))
            {
                $cas = new CAServer();
                $this->di['session'] ->set('uid', $uid);
                $this->di['session'] ->set('uinfo', array('name' => '', 'mobile' => $mobile));
                $cas->casSave(0, 0);
                return self::SUCCESS;
            }
            else 
                return self::ERROR;
        }
        
    }

    public function wbRegister($passwd, $mobile, $email = '', $pic= '', $wbUid = '')
    {
        $user = $this->user->getUidByMobi($mobile);
        if($user['u_id'])
            return self::MOBI_EXITE;
        else
        {
            $regTime = $_SERVER['REQUEST_TIME'];
            $passwd = \Ucenter\Utils\Inputs::makeSecert($passwd, $regTime);
            if( $uid = $this->user->addUser( $email, $passwd, $mobile, $regTime, $pic, '', $wbUid))
            {
                $cas = new CAServer();
                $this->di['session'] ->set('uid', $uid);
                $this->di['session'] ->set('uinfo', array('name' => '', 'mobile' => $mobile));
                $cas->casSave(0, 0);
                return self::SUCCESS;
            }
            else 
                return self::ERROR;
        }
    }

    public function wxRegister($passwd, $mobile, $email = '', $pic= '', $openId = '')
    {
        $user = $this->user->getUidByMobi($mobile);
        if($user['u_id'])
            return self::MOBI_EXITE;
        else
        {
            $regTime = $_SERVER['REQUEST_TIME'];
            $passwd = \Ucenter\Utils\Inputs::makeSecert($passwd, $regTime);
            if( $uid = $this->user->addUser( $email, $passwd, $mobile, $regTime, $pic, '', '', $openId))
            {
                $cas = new CAServer();
                $this->di['session'] ->set('uid', $uid);
                $this->di['session'] ->set('uinfo', array('name' => '', 'mobile' => $mobile));
                $cas->casSave(0, 0);
                return self::SUCCESS;
            }
            else 
                return self::ERROR;
        }
    }

    public function alipayRegister($passwd, $mobile, $email = '', $pic= '', $openId = '')
    {
        $user = $this->user->getUidByMobi($mobile);
        if($user['u_id'])
            return self::MOBI_EXITE;
        else
        {
            $regTime = $_SERVER['REQUEST_TIME'];
            $passwd = \Ucenter\Utils\Inputs::makeSecert($passwd, $regTime);
            if( $uid = $this->user->addUser( $email, $passwd, $mobile, $regTime, $pic, '', '', '', $openId))
            {
                $cas = new CAServer();
                $this->di['session'] ->set('uid', $uid);
                $this->di['session'] ->set('uinfo', array('name' => '', 'mobile' => $mobile));
                $cas->casSave(0, 0);
                return self::SUCCESS;
            }
            else 
                return self::ERROR;
        }
    }


    /**
     * [login 用户注册模块]
     * @param  string $tel    [手机]
     * @param  string $passwd [密码]
     * @param  integer $uid [用户id]
     * @return integer  [登陆成功，返回１，登陆失败返回０,手机未注册返回２]
     */
    public function login($tel, $passwd)
    {
        $res = $this->user->getUserInfoByMobi($tel, $passwd);
        if(empty($res['u_id']))
            return self::MOBI_NON_EXITE;
        else
        {
            $pwd = \Ucenter\Utils\Inputs::makeSecert($passwd, $res['u_regtime']);
            if($pwd === $res['u_pass'])
            {
                $this->di['session'] ->set('uid', $res['u_id']);
                $this->di['session'] ->set('uinfo', array('name'=>$res['u_name'], 'mobile'=>$tel));
                return self::SUCCESS;
            }
            else
                return self::PASS_ERR;
        }
    }

    /**
     * [existTel 判断手机是否存在]
     * @param  string $mobile [手机号]
     * @return integer[存在返回１，不存在返回０]
     */
    public function existTel($mobile)
    {
        $user = $this->user->getUidByMobi($mobile);
        if($user['u_id'])
            return self::SUCCESS;
        else
            return self::ERROR;
    }

    /**
     * [changePwd description]
     * @param  [string] $uid    [用户id]
     * @param  [string] $newPwd [新密码]
     * @param  [string] $regtime [注册时间]
     * @return [bool]
     */
    public function changePwd($uid, $newPwd)
    {
        $userInfo = $this->user->getUserInfo($uid);
        $pwd = \Ucenter\Utils\Inputs::makeSecert($newPwd, $userInfo['u_regtime']);
        return $this->user->updatePwd($uid, $pwd);
    }

    /**
     * [changeNickname description]
     * @param  [type] $uid  [用户id]
     * @param  [type] $name [昵称]
     * @return [type]       [description]
     */
    public function changeNickname($uid, $name)
    {
        if($this->user->updateNickname($uid, $name))
        {
            $uinfo = $this->di['session'] ->get('uifo');
            $this->di['session'] ->set('uifo', array('name' => $name, 'mobile' => $uinfo['mobile']));
        }
        else
            return self::ERROR;
    }

    /**
     * 获取用户信息
     * @param unknown $uid
     */
    public function getUserInfo($uid)
    {
        return  $this->user->getUserInfo($uid);
    }

    /**
     * 设置邮箱
     * @param unknown $uid
     * @param unknown $email
     */


    public function setEmail($uid, $email)
    {
        return  $this->user->updateEmail($uid, $email);
    }

    /**
     * 判断邮箱是否存在
     * @param unknown $email
     */
    public function isEmailExist($email)
    {
        return  $this->user->getEmailByEmail($email);
    }


    /**
     * [bingQq Q绑定用户]
     * @param  [int] $uid    [用户id]
     * @param  string $OpenId [qq 用户ID]
     * @return [bool]
     */
    public function bingQq($uid, $OpenId)
    {
        return $this->user->updateUserQqBind($uid, $OpenId);
    }

    /**
     * [bingWb 微薄绑定用户]
     * @param  [int] $uid    [用户id]
     * @param  string $wbUid  [微薄用户ID]
     * @return [bool]
     */
    public function bingWb($uid, $wbUid)
    {
        return $this->user->updateUserWbBind($uid, $wbUid);
    }

    public function bingWX($uid, $openid)
    {
        return $this->user->updateUserWxBind($uid, $openid);
    }

    public function bindAlipay($uid, $openid)
    {
        return $this->user->updateUserAlipayBind($uid, $openid);
    }

    /**
      * [bingWb 微薄QQ解绑]
      * @param  [int] $uid    [用户id]
      * @param  string $type [区分qq,sina]
      * @return [bool]
     */
    public function unBingQqWb($uid, $type)
    {
        if($type === 'qq')
            return $this->user->updateUserQqBind($uid, '');
        if($type === 'sina')
            return $this->user->updateUserWbBind($uid, '');
        if($type === 'wx')
            return $this->user->updateUserWxBind($uid, '');
        if($type === 'alipay')
            return $this->user->updateUserAlipayBind($uid, '');
    }

    /**
     * 更换邮箱
     * @param unknown $uid
     * @param unknown $email
     * @param unknown $verify
     */
    public function resetEmail($uid,$email)
    {
        return $this->user->updateEmailVerify($uid, $email, '1');
    }

    /**
     * [changeAvatar 更换头像]
     * @param  [int] $uid    [用户id]
     * @param  [string] $picUrl [头像地址]
     * @return [type]         [description]
     */
    public function changeAvatar($uid, $picUrl)
    {
        if($this->user->updateUserAvatar($uid, $picUrl))
            return self::SUCCESS;
        else
            return self::ERROR;
    }

    /**
     * [getUid 通过openid,wbuid获取用户uid]
     * @param  string $openId [qq openid]
     * @param  string $type   [qq ,wb]
     * @return [type]         [description]
     */
    public function getUinfo($qqWbId = '', $type)
    {
        if($type == 'wb')
            $t = 'u_wb_uid';
        else if($type == 'qq')
            $t = 'u_qq_uid';
        else if($type == 'wx')
            $t = 'u_wx_uid';
        else if($type == 'alipay')
            $t = 'u_alipay_uid';
        else
            return self::AUTH_ERROR;
        if($uinfo=$this->user->getUinfoByQqWb($qqWbId, $t))
            return $uinfo;
        else
            return self::AUTH_ERROR;
    }
    /**
     * 
     * @param unknown $mobile
     */
    public function getUid($mobile)
    {
        return $this->user->getUidByMobi($mobile);
    }

    /**
     * 判断昵称是否已存在
     * @param string $nickname
     * @return int [1:存在 0：不存在]
     */
    public function isNickExist($nickname)
    {
        if($this->user->getUserByNick($nickname))
        {
            return self::SUCCESS;
        }
        else
        {
            return self::ERROR;
        }
    }

    /**
     * [updateUserLoginTime 更新用户最后登陆时间]
     * @param  [int] $mobile  [用户手机]
     * @param  [string] $time [时间戳]
     * @return [type]       [description]
     */
    public function updateUserLoginTime($mobile, $time)
    {
        if($this->user->updateLoginTime($mobile, $time))
        {
            return self::SUCCESS;
        }
        else
        {
            return self::ERROR;
        }
    }
}
