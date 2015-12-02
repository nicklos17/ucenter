<?php
    return array(
        'domain'=>'http://test.my.yunduo.com',
        //thrift服务
        'thrift' => array('ip' =>'127.0.0.1','port' => '6013'),
        //redis配置
        'redis' => array('server' => '127.0.0.1', 'port' => '6379', 'timeout' => '2.5', 'dbname' => 13),

        //+++++++++++++短信文案++++++++++++++++
        //注册
        'regMsg' => '云朵儿童安全鞋：验证码：%s。感谢您注册成为云朵会员，该验证码30分钟内有效。',
        //忘记密码
        'resetMsg' => '云朵儿童安全鞋：验证码：%s。您正在使用找回密码功能，如果不是您本人操作，请忽略，该验证码30分钟内有效。',
        //修改密码
        'changeMsg' => '云朵儿童安全鞋：验证码：%s。您正在使用修改密码功能，如果不是您本人操作，请忽略，该验证码30分钟内有效。',
        //更改绑定邮箱
        'chaEmailMsg' => '云朵儿童安全鞋：验证码：%s。您正在使用更改邮箱功能，如果不是您本人操作，请忽略，该验证码30分钟内有效。',
        //发邮件配置 $this->user-> $this->user->
        'emailFrom' => 'noreply@yunduo.com',
        'emailName' => 'noreply@yunduo.com',
        'mail_subject' => '云朵安全验证',
        'emailConf' => array (
                'protocol'  => 'smtp',
                'smtp_host' => 'smtp.exmail.qq.com',
                'smtp_user' => 'noreply@yunduo.com',
                'smtp_pass' => 'y123456unduo',
                'smtp_port' => 465,
                'charset'   => 'utf-8',
                'wordwrap'  => FALSE,
                'mailtype'  => 'html',
                'smtp_crypto'   => 'ssl',
                'newline'   => "\r\n"
        ),
        //用户图像上传默认路径
        'avatar'=>'public/images/avatar',
        //用户头像访问的路径 
        'avatarAccess'=>'images/avatar',
        //同个用户两次获得验证码的间隔
        'haveCap' => '60',
        //验证码时效:半小时
        'capValid' => '1800',
        //邮箱验证url
        'emailValidurl' => 'http://test.my.yunduo.com/mailverify/validemail',

        //邮箱验证过期时间
        'emailinvalid' => '86400',

        //静态资源服务器地址
        'staticServer' => '/',
        //pc版主页
        'siteUrl' => 'http://test.www.yunduo.com/',
        //手机版主页
        'mobileUrl' => 'http://test.m.yunduo.com/',
        //商城主页
        'mallUrl' => 'http://test.mall.yunduo.com/',
        //移动商城
        'mobiMallUrl' => 'http://test.m.mall.yunduo.com/',

       'emailMsg' => array(
            '1' => '邮件不存在',
            '2' => '邮件已激活',
            '3' => '邮件失效',
            '4' => '验证失败',
            '5' => '更改邮箱失败',
            '6' => '验证成功',
        ),

/************ 输出错误页面的信息 ***************/
    'flagMsg' => array(
            '10000' => '服务器出现异常，请稍候重试',
            '10001' => '用户不存在',
            '10002' => '手机号已存在',
            '10003' => '手机号格式错误',
            '10004' => '手机号未注册',
            '10006' => '邮箱错误',
            '10008' => '前后两次密码不一致',
            '10009' => '创建钱包失败',
            '10010' => '密码错误',
            '10011' => '验证码错误',
            '10012' => '验证码失效',
            '10013' => '名称错误',
            '10014' => '名称已被占用',
            '10015' => '邮箱已存在',
            '10016' => '注册失败',
            '10017' => '修改密码失败',
            '10018' => '短信发送失败',
            '10019' => '用户没有同意条款',
            '10020' => '上传文件失败',
            '10021' => '邮件不存在',
            '10022' => '邮件已激活',
            '10023' => '邮件失效',
            '10024' => '验证失败',
            '10025' => '更改邮箱失败',
            '10026' => 'sina UID不合法',
            '10027' => '授权失败',
            '10028' => '昵称为空',
            '10029' => '第三方绑定失败',
            '10030' => '', //需要图片验证码
            '10031' => '非法操作',

        ),
    );