<?php

namespace Ucenter\Mdu\Models;

class EmailVerifyModel extends ModelBase
{

    /**
     * @param unknown $uid
     * @param unknown $email
     * @param unknown $string
     * @return unknown
     */
    public function innserEmailVerify($uid, $email, $secert)
    {
        $sql = " INSERT INTO cloud_email_verify (u_id, u_email,ev_string,ev_addtime) VALUES (:u_id, :u_email, :ev_string,:ev_addtime)" ;
        return $this->db->query( $sql,
            array (
                'u_id'=> $uid,
                'u_email'=> $email,
                'ev_string'=> $secert,
                'ev_addtime'=> $_SERVER['REQUEST_TIME'],
            )
        );
    }

    /**
     * 更改邮箱激活时间
     * @param unknown $uid
     * @param unknown $verifytime
     * @return unknown
     */
    public function updateEmailVtime($uid, $verifytime)
    {
        return $this->db->query('UPDATE cloud_email_verify SET ev_verifytime= :verifytime WHERE u_id= :uid',
            array(
                'uid'=> $uid,
                'verifytime'=> $verifytime,
            )
        );
    }

    /**
     * 获取添加时间和验证时间
     * @param unknown $uid
     * @param unknown $secert
     */
    public function getByUidSecertTime($uid, $secert)
    {
        return $this->db->query('SELECT ev_addtime as addtime, ev_verifytime as verifytime from cloud_email_verify WHERE u_id = ? AND ev_string = ? limit 1',
            array(
                $uid,
                $secert
            )
        )->fetch();
    }

    /**
     * 根据uid获取邮箱验证信息
     */
    public function getVerify($uid, $secert)
    {
        return $this->db->query('SELECT ev_addtime as addtime, ev_verifytime as verifytime from cloud_email_verify WHERE u_id = ? limit 1',
            array(
                $uid,
                $secert
                )
        )->fetch();
    }

}