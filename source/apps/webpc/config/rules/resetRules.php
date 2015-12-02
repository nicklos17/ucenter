<?php
include __DIR__.'/baseRules.php';
$rules['checkcap'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('regtype','captcha','mobile','codeImg')
        ),
        'regtype' => array(
                'required' => 1,
                'valueis' => '7',
                'filters' => 'trim',
                'msg' => '验证码类型错误'
        ),
        'captcha' => array(
                'required' => 1,
                'filters' => 'trim',
                'regex' => '/^\d{4}$/',
                'msg' => '请输入正确的４位验证码'
        ),
        'codeImg' => array(
                'required' => '',
                'filters' => 'trim',
                'length' => 4,
                'msg' => '请输入正确的４位验证码'
        ),
        'mobile'=>mobile(),
);
$rules['setPwd'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('passwd','repwd')
        ),
        'passwd' => passwd(),
        'repwd' => confirmpass(),
);

return $rules;
