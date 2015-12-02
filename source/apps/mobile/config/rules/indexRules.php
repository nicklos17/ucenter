<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
        '_request' => array('soap', 'secure', 'ajax'),
        '_method' => array(
             'post' => array('mobile', 'smscaptach', 'passwd', 'confirmpass', 'readme', 'email'),
        //    'get' => array('email'),
            // 'cookie' => array('xxxx'),
        ),

        'mobile' => mobile(),

        'smscaptach' => array(
            'required' => 1,
            'length' => 6,
            'filters' => 'trim',
            'regex' => '/^\w[6]$/',
            'msg' => '请输入6位短信验证码'
        ),

        'passwd' =>passwd(),

        'confirmpass' => confirmpass(),

        'readme' => array(
            'required' => 1,
            'valueis' => 'yes',
            'filters' => 'trim',
            'msg' => '请阅读并同意使用条款',
        ),

        'email' => email(),

        'age' => array(
            'required' => 1,
            'between' => array(3, 12),
            'filters' => 'trim',
            'msg' => '年龄必须在3-12岁之间'
        ),

        'sex' => array(
            'required' => 1,
            'range' => array(1, 3),
            'filters' => 'trim',
            'sanitize' => function($v){
                return $v+1;
            },
            'msg' => '请选择宝贝性别',
        ),

        'size' => array(
            'required' => 1,
            'rangeout' => array(39, 25),
            'filters' => 'trim',
            'msg' => '对不起，39码和25码已经售罄',
        ),

        'orderids' => array(
            'required' => 1,
            'nums' => array(1),
            'filters' => 'trim',
            'msg' => '请至少选择一个订单',
        ),

        'avatar' => array(
            'required' => 1,
            'default' => array('xx.gif'),
            'nums' => array(1, 5),
            'filetype' => array('gif', 'jpg', 'png'),
            'filesize' => 10*1024*1024,
            'msg' => '请选择以gif，jpg，png结尾的图片',
        ),
);
$rules['login'] = array(
    '_request' => array('ajax'), 
    '_method' => array(
        'post' => array('mobile',  'passwd')
    ),
    'mobile' => mobile(),
    'passwd' => passwd()
);
$rules['register'] = array(
        '_request' => array('ajax'),
        '_method' => array(
            'post' => array('mobile',  'passwd','confirmpass', 'email','captcha','agree', 'regtype')
        ),
        'mobile' => mobile(),
        'passwd' => passwd(),
        'email' => optionalEmail(),
        'confirmpass' => confirmpass(),
        'regtype'=>array(
            'required' => 1,
            'valueis' => '1',
            'filters' => 'trim',
            'msg' => '验证码类型错误'
        ),
        'captcha'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d{4}$/',
            'msg' => '请输入正确的４位验证码'
        ),
        'agree'=>array(
            'required' => 1,
            'valueis' => 'on',
            'filters' => 'trim',
            'msg' => '请先阅读网站使用条款和隐私条款'
        ),
        'codeImg'=>array(
            'required' => '',
            'filters' => 'trim',
            'length' => 4,
            'msg' => '请输入正确的４位验证码'
        ),
);

$rules['adduserinfo'] = array(
    '_request' => array('ajax'),
    '_method' => array(
        'post' => array('mobile', 'passwd', 'confirmpass', 'email', 'captcha', 'agree', 'openid', 'wbuid', 'regtype', 'pic')
    ),
    'mobile' => mobile(),
    'passwd' => passwd(),
    'email' => optionalEmail(),
    'confirmpass' => confirmpass(),
    'regtype'=>array(
        'required' => 1,
        'valueis' => '1',
        'filters' => 'trim',
        'msg' => '验证码类型错误'
    ),
    'captcha'=>array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d{4}$/',
        'msg' => '请输入正确的４位验证码'
    ),
    'agree'=>array(
        'required' => 1,
        'valueis' => 'on',
        'filters' => 'trim',
        'msg' => '请先阅读网站使用条款和隐私条款'
    ),
    'openid'=>array(
        'required' => 0,
        'length' => 32,
        'filters' => 'trim',
        'msg' => '错误的openid'
    ),
    'wbuid'=>array(
        'required' => 0,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '错误的uid'
    ),
        'pic'=>array(
            'required' => 0,
            'length' => array(10,100),
            'filters' => 'trim',
            'msg' => '错误的图片地址'
    ),
);

$rules['existtel'] = array(
        '_request' => array('ajax'),
        '_method' => array(
            'post' => array('mobile')
        ),
        'mobile' => mobile(),
);
return $rules;
