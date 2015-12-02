<?php

namespace Ucenter\Mdu\Models;

class UsersModel extends ModelBase
{
    /**
     * [login 用户登陆判断]
     * @param  String $tel    [手机号]
     * @param  Ｓtring $passwd [用户输入的密码]
     * @return integer   [用户登陆成功返回用户uid,失败返回０]
     */
    public function getUserInfoByMobi( $tel,$passwd ) 
    {
        return $this->db->query('SELECT u_id, u_mobi, u_name,u_pic, u_regtime, u_last_logintime, u_level, u_email, u_email_verify, u_wb_uid, u_qq_uid, u_wx_uid, u_alipay_uid,u_pass  FROM cloud_users WHERE u_mobi=:tel LIMIT 1 ',
            array(
                'tel'  =>  $tel
            )
        )->fetch();
    }

    /**
     * [pingMysql 保持swoole mysql连接]
     */
    public function pingMysql()
    {
        return $this->db->query('SELECT \'timer run, keep db conn alive\'')->fetch();
    }
    
    /**
     * [getUidByMobi 判断手机号是否被注册]
     * @param  string $mobile [手机号]
     */
    public function getUidByMobi($mobile)
    {
        return $this->db->query('SELECT u_id FROM cloud_users where u_mobi=:mobile  LIMIT 1',
            array(
                'mobile' => $mobile
            )
        )->fetch();
    }

    /**
     * [addUser 新增用户]
     * @param  string  $passwd   [用户输入的密码]
     * @param  string  $email   [用户邮箱]
     * @param  string $mobile [手机号]
     * @return　integer [1添加成功，０失败]
     */
    public function addUser($email, $passwd, $mobile, $regTime, $pic, $qqOpenId = '', $wbUid = '', $wxOpenid = '', $aliOpenid = '')
    {
        $sql =' INSERT INTO cloud_users ( u_email, u_pass, u_mobi, u_regtime, u_qq_uid, u_wb_uid, u_pic, u_wx_uid, u_alipay_uid '
            .' ) VALUES ( :email, :passwd, :mobile, :regtime, :qq, :wb, :pic, :wx, :ali)'; 
        try
        {
            $this->db->begin();
            $res=$this->db->query($sql,
                array (
                    'email' => $email,
                    'passwd' => $passwd,
                    'mobile' => $mobile ,
                    'regtime' => $regTime,
                    'qq' => $qqOpenId,
                    'wb' => $wbUid,
                    'pic'=>$pic,
                    'wx' => $wxOpenid,
                    'ali' => $aliOpenid
                )
            );
            $u_id = $this->db->lastInsertId();
            if($u_id)
            {
                if($this->db->execute('INSERT INTO cloud_user_wallets(u_id) VALUES (?) ', array( $u_id)))
                    $this->db->commit();
                else
                {
                    $this->db->rollback();
                    return 0;
                }
            }
            return $u_id;
        }
        catch( E $e) 
        {
            $this->db->rollback();
            return 0;
        }
    }

    /**
     * [updatePwd description]
     * @param  [string] $uid    [用户id]
     * @param  [string] $newPwd [新密码]
     * @param  [string] $regtime [注册时间]
     * @return [bool]
     */
    public function updatePwd($uid, $pwd) 
    {
        return $this->db->execute('UPDATE cloud_users SET u_pass = :pass WHERE u_id = :uid',
            array(
                'uid' => $uid,
                'pass' => $pwd,
            )
        );
    }
    
     public function updatePwdByMobi($mobi, $pwd) 
    {
        return $this->db->execute('UPDATE cloud_users SET u_pass = :pass WHERE u_mobi = :tel',
            array(
                'tel' => $mobi,
                'pass' => $pwd,
            )
        );
    }

    /**
     * [updateNickname description]
     * @param  [type] $uid  [用户id]
     * @param  [type] $name [昵称]
     * @return [type]       [description]
     */
    public function updateNickname($uid, $name)
    {
        return $this->db->execute('UPDATE cloud_users SET u_name = :name WHERE u_id = :uid',
            array(
                'uid' => $uid,
                'name' => $name,
            )
        );
    }

    /**
     * 获取用户信息
     * @param unknown $uid
     * @return unknown
     */
    public function getUserInfo($uid)
    {
        return $this->db->query('SELECT a.u_id,b.uw_coins, u_mobi, u_name,u_pic, u_regtime, u_last_logintime, u_level, u_email, u_email_verify, u_wb_uid, u_qq_uid, u_wx_uid, u_alipay_uid,u_pass FROM
            cloud_users as a INNER JOIN  cloud_user_wallets as b ON a.u_id=b.u_id WHERE a.u_id = ? AND u_status = 1 limit 1',
                array(
                    $uid
                )
        )->fetch();
    }

    /**
     * 更新或设置邮箱
     * @param unknown $uid
     * @param unknown $email
     * @return unknown
     */
    public function updateEmail($uid,$email)
    {
        return $this->db->execute('UPDATE cloud_users SET u_email= :email WHERE u_id= :uid',
            array(
                'uid' => $uid,
                'email' => $email,
            )
        );
    }

    /**
     * 邮件验证
     * @param unknown $uid
     * @return unknown
     */
    public function updateEmailStatus($uid,$status)
    {
        return $this->db->execute('UPDATE cloud_users SET u_email_verify = :status WHERE u_id= :uid',
            array(
                'uid' => $uid,
                'status' => $status,
            )
        );
    }

    /**
     * [updateUserQqBind QQ绑定账号]
     * @param  [int] $uid    [用户ID]
     * @param  [string] $openId [QQ用户唯一ID]
     * @return [bool] 
     */
    public function updateUserQqBind($uid, $openId)
    {
        return $this->db->execute('UPDATE cloud_users SET u_qq_uid = :openid WHERE u_id = :uid',
            array(
                'uid' => $uid,
                'openid' => $openId
            )
        );
    }

        /**
     * [updateUserWbBind 微博绑定账号]
     * @param  [int] $uid    [用户ID]
     * @param  [string] $wbUid [微博用户唯一ＵID]
     * @return [bool] 
     */
    public function updateUserWbBind($uid, $wbUid)
    {
        return $this->db->execute('UPDATE cloud_users SET u_wb_uid = :wbuid WHERE u_id = :uid',
            array(
                'uid' => $uid,
                'wbuid' => $wbUid
            )
        );
    }

    /**
     * [updateUserWbBind 微信绑定账号]
     * @param  [int] $uid    [用户ID]
     * @param  [string] $wbUid [微博用户唯一ＵID]
     * @return [bool] 
     */
    public function updateUserWxBind($uid, $wxUid)
    {
        return $this->db->execute('UPDATE cloud_users SET u_wx_uid = :wxuid WHERE u_id = :uid',
            array(
                'uid' => $uid,
                'wxuid' => $wxUid
            )
        );
    }

    /**
     * [updateUserWbBind 支付宝绑定账号]
     * @param  [int] $uid    [用户ID]
     * @param  [string] $wbUid [微博用户唯一ＵID]
     * @return [bool] 
     */
    public function updateUserAlipayBind($uid, $alipayUid)
    {
        return $this->db->execute('UPDATE cloud_users SET u_alipay_uid = :alipayuid WHERE u_id = :uid',
            array(
                'uid' => $uid,
                'alipayuid' => $alipayUid
            )
        );
    }

    /**
     * 从设邮箱
     * @param unknown $uid
     * @param unknown $email
     * @param unknown $verify
     * @return unknown
     */
    public function updateEmailVerify($uid, $email, $verify)
    {
        return $this->db->execute('UPDATE cloud_users SET u_email = ?,u_email_verify = ? WHERE u_id = ?',
            array(
                $email,
                $verify,
                $uid
            )
        );
    }

    /**
     * 获取邮箱
     * @param unknown $email
     */
    public function getEmailByEmail($email)
    {
        return $this->db->query('SELECT u_email FROM cloud_users WHERE u_email = ?',
            array(
                $email
            )
        )->fetch();
    }

    /**
     * 修改用户投降
     * @param unknown $uid
     * @param unknown $email
     * @param unknown $verify
     * @return unknown
     */
    public function updateUserAvatar($uid, $picUrl)
    {
        return $this->db->execute('UPDATE cloud_users SET u_pic = ? WHERE u_id = ?',
            array(
                $picUrl,
                $uid
            )
        );
    }

    /**
     * [getUidByQqWb 通过openid获取uid]
     */
    public function getUinfoByQqWb($openid,$t)
    {
        $sql =  "SELECT u_id, u_name, u_mobi FROM cloud_users  where $t = ?  LIMIT 1";
        $uinfo = $this->db->query($sql,
            array(
                $openid
            )
        )->fetch();

        return $uinfo;
    }

    /**
     * 根据用户名获取User
     * @param string $nickname
     */
    public function getUserByNick($nickname)
    {
        return $this->db->query('SELECT `u_id` FROM `cloud_users` WHERE `u_name` = ?',
            array(
                $nickname
            )
        )->fetch();
    }

    /**
     * [updateLoginTime 更新用户最后登陆时间]
     * @param  [int] $mobile [用户手机]
     * @param  [string] $time [时间戳]
     * @return [bool] return
     */
    public function updateLoginTime($mobile, $time)
    {
        return $this->db->execute('UPDATE cloud_users SET u_last_logintime = ? WHERE u_mobi = ?',
            array(
                $time,
                $mobile
        ));
    }

    public function updateLevel($uid)
    {
        return $this->db->execute("UPDATE cloud_users SET u_level = u_level + 1 WHERE u_id = $uid");
    }

    public function checkThirdLogin($uTags, $plat)
    {
        if($plat == 1)
        {
            $str = 'u_wb_uid = "'.$uTags .'"';
        }
        elseif($plat == 3)
        {
            $str = 'u_qq_uid = "'.$uTags.'"';
        }
        return $query = $this->db->query('SELECT u_id, u_mobi, u_pass, u_name, u_status, u_pic, u_level, u_wb_uid, u_qq_uid FROM cloud_users WHERE '. $str .' AND u_status=1 limit 1')->fetch();
    }

    public function oauthBind($oauthType, $uid, $oauthUid, $picUrl = '')
    {
        if($oauthType == '1')
        {
            $data['u_wb_uid'] = $oauthUid;
            $sql = "UPDATE cloud_users SET u_wb_uid = :u_wb_uid";
        }
        elseif($oauthType == '3')
        {
            $data['u_qq_uid'] = $oauthUid;
            $sql = "UPDATE cloud_users SET u_qq_uid = :u_qq_uid";
        }
        else
        {
            return false;
        }
        if($picUrl != '')
        {
            $data['u_pic'] = $picUrl;
            $sql .= ", u_pic = :u_pic";
        }
        $data['uid'] = $uid;
        $sql .= " WHERE u_id = :uid";

        return $this->db->execute($sql, $data);
    }

    public function unbindThird($uid, $oauthType)
    {
        if($oauthType == '1')
        {
            $res = $this->db->execute('UPDATE cloud_users set u_wb_uid = "" where u_id = ? AND u_status = 1 LIMIT 1', array($uid));
        }
        elseif($oauthType == '3')
        {
            $res = $this->db->execute('UPDATE cloud_users set u_qq_uid = "" where u_id = ? AND u_status = 1 LIMIT 1', array($uid));
        }
        if($res == FALSE)
            return 0;
        else
            return 1;
    }

    public function modifyUser($name, $uid, $pic)
    {
        $data = array('u_name' => $name);
        $sql = "UPDATE cloud_users SET u_name = :u_name";
        if($pic)
        {
            $data['u_pic'] = $pic;
            $sql .= ", u_pic = :u_pic";
        }
        $sql .= " WHERE u_id = :uid";
        $data['uid'] = $uid;
        $res = $this->db->execute($sql, $data);
        if($res == FALSE)
            return 0;
        else
            return 1;
    }

    public function userInfoByIds($uids)
    {
        return $this->db->query('SELECT u_id, u_name, u_mobi, u_pic FROM cloud_users WHERE u_id in(' . $uids . ') AND u_status = 1  ORDER BY u_id DESC')->fetchAll();
    }

    /**
     * 第三方注册
     * @param str $oauthType 第三方类型：1-新浪 3-qq
     * @param unknown $mobi
     * @param unknown $pass
     * @param unknown $email
     * @param unknown $sinaUid
     * @param unknown $nickname
     * @param unknown $regtime
     */
    public function oauthReg($oauthType, $mobi, $pass, $email, $oauthUid, $nickname, $pic, $regtime)
    {
        $data = array(
                'u_mobi' => $mobi,
                'u_pass' => $pass,
                'u_email' => $email,
                'u_name' => $nickname,
                'u_pic' =>$pic,
                'u_regtime' => $regtime
        );
        if($oauthType == '1')
        {
            $data['u_wb_uid'] = $oauthUid;
            $sql = "INSERT INTO cloud_users(u_mobi, u_pass, u_email, u_name, u_pic, u_regtime, u_wb_uid) VALUES(:u_mobi, :u_pass, :u_email, :u_name, :u_pic, :u_regtime, :u_wb_uid)";
        }
        elseif($oauthType == '3')
        {
            $data['u_qq_uid'] = $oauthUid;
            $sql = "INSERT INTO cloud_users(u_mobi, u_pass, u_email, u_name, u_pic, u_regtime, u_qq_uid) VALUES(:u_mobi, :u_pass, :u_email, :u_name, :u_pic, :u_regtime, :u_qq_uid)";
        }
        else
        {
            return false;
        }
        $this->db->execute($sql, $data);
        return $this->db->lastInsertId();
    }

    //******************************************swoole***********************//
    /**
     *注册
     * @param unknown $mobi
     * @param unknown $pass
     * @param unknown $pic
     * @param unknown $regtime
     * @param unknown $email
     */
    public function regFromSwoole($mobi, $pass, $pic, $email, $regtime)
    {
        $data = array(
                        'u_mobi' => $mobi,
                        'u_pass' => $pass,
                        'u_pic' => $pic,
                        'u_email' => $email,
                        'u_regtime' => $regtime
                        );
        $this->db->execute('INSERT INTO cloud_users(u_mobi, u_pass, u_pic, u_email, u_regtime) VALUES(:u_mobi, :u_pass, :u_pic, :u_email, :u_regtime)', $data);
        return $this->db->lastInsertId();
    }
}