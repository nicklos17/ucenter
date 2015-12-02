<?php
include __DIR__.'/baseRules.php';

$rules['checkcap'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('regtype','captcha','codeImg')
        ),
        'regtype'=>array(
                'required' => 1,
                'valueis' => '11',
                'filters' => 'trim',
                'msg' => '验证码类型错误'
        ),
        'captcha'=>array(
                'required' => 1,
                'filters' => 'trim',
                'regex' => '/^\d{4}$/',
                'msg' => '请输入正确的４位验证码'
        ),
      'codeImg'=>array(
                'required' => '',
                'filters' => 'trim',
                'length' => 4,
                'msg' => '请输入正确的４位验证码'
        ),
);
$rules['change'] = array();
$rules['success'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'get' => array('email')
        ),
        'email' => array(
                'required' => 1,
                'filters' => 'trim',
                'regex' => '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/',
                'msg' => '请输入正确的邮箱'
        ),

);
$rules['resetEmail'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('email')
        ),
        'email' => array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/',
            'msg' => '请输入正确的邮箱'
        ),
);
return $rules;
