<?php

namespace Ucenter\Mdu;

use Ucenter\Mdu\Models\EmailVerifyModel;
use Ucenter\Mdu\Models\UsersModel;

class EmailVerifyModule extends ModuleBase
{
    protected $emailVerify,$users;

    const NO_EMAIL = 1;
    const VERI_FIED = 2;
    const MAIL_UN_VALID =3;
    const VERIFY_FAIL = 4;
    const EMAIL_FAIL =5;
    const SUCCESS = 6;
    
    public function __construct()
    {
        $this->emailVerify = $this->initModel('\Ucenter\Mdu\Models\EmailVerifyModel');
        $this->users = $this->initModel('\Ucenter\Mdu\Models\UsersModel');
    }

    /**
     * [validemail description]
     * @param  [type] $uid        [description]
     * @param  [type] $secert     [description]
     * @param  [type] $verifytime [description]
     * @return [type]             [1 邮件不存在,2 邮件已激活,3 邮件失效,4 验证失败,5 更改邮箱失败,6 成功]
     */
    public function validemail($uid, $secert, $verifytime)
    {
        $mailInfo = $this->emailVerify->getByUidSecertTime($uid, $secert);
        if(empty($mailInfo))
        {
            return self::NO_EMAIL;
        }
        if($mailInfo['verifytime'] != 0)
        {
            return self::VERI_FIED;
        }
        if($_SERVER['REQUEST_TIME'] - $mailInfo['addtime'] > $this->di['sysconfig']['emailinvalid'])
        {
            return self::MAIL_UN_VALID;
        }
        if($this->emailVerify->updateEmailVtime($uid, $_SERVER['REQUEST_TIME'],$secert))
        {
            //将数据库字段设置为已验证
            if($this->users->updateEmailStatus($uid, '3'))
            {
                return self::SUCCESS;
            }
            else
            {
                return self::EMAIL_FAIL;
            }
        }
        else
        {
            return self::VERIFY_FAIL;
        }
    }
}
