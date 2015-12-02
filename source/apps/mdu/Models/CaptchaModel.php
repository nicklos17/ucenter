<?php

namespace Ucenter\Mdu\Models;

class CaptchaModel extends ModelBase
{
    /**
     * 获取最近一条验证码的信息
     */
    public function getLastCapthaInfo($mobi, $type)
    {
        return $this->db->query('select mc_captcha, mc_addtime, mc_validtime from cloud_mobi_captcha where mc_mobi = ? AND mc_type = ? ORDER BY mc_addtime DESC limit 1',
            array(
                $mobi,
                $type
                )
        )->fetch();
    }

    /**
     * 根据验证码获取生成时间和验证时间
     * @param unknown $mobi
     * @param unknown $type
     * @param unknown $captcha
     */
    public function getCapthaTime($mobi, $type, $captcha)
    {
        return $this->db->query('SELECT mc_addtime, mc_validtime FROM cloud_mobi_captcha 
            WHERE mc_mobi = :mc_mobi AND mc_type = :mc_type AND mc_captcha = :mc_captcha ORDER BY mc_addtime DESC LIMIT 1',
            array(
                'mc_type'=> $type,
                'mc_mobi'=> $mobi,
                'mc_captcha'   => $captcha,
            )
        )->fetch();
    }

    /**
     * 验证码入库
     * @param unknown $mobi
     * @param unknown $type
     * @param unknown $captcha
     * @param unknown $addtime
     */
    public function addCaptcha( $mobi, $type, $nowtime, $captcha)
    {
        $res=$this->db->query('INSERT INTO cloud_mobi_captcha (mc_type, mc_mobi, mc_captcha,mc_addtime) VALUES (:mc_type,:mc_mobi,:mc_captcha,:mc_addtime)',
            array(
                'mc_type'=> $type,
                'mc_mobi'=> $mobi,
                'mc_captcha'=> $captcha,
                'mc_addtime'=> $nowtime,
            )
        ) ;

        return  $this->db->lastInsertId();
    }

    /**
     * 完成验证码的验证
     * @param unknown $mobi
     * @param unknown $captcha
     * @param unknown $type
     * @param unknown $validtime
     */
    public function updateCaptcha($mobi, $captcha, $type, $validtime)
    {
        return $this->db->execute('UPDATE cloud_mobi_captcha SET mc_validtime= :validtime WHERE mc_mobi= :mobi and mc_captcha=:captcha and mc_type=:type',
            array(
                'mobi'=> $mobi,
                'captcha'=> $captcha,
                'type'=> $type,
                'validtime'=>$validtime
            )
        );
    }
}