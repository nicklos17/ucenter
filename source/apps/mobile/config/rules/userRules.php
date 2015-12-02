<?php

include __DIR__.'/baseRules.php';


$rules['editPasswd'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('regtype','captcha','passwd','repwd')
        ),
        'regtype'=>array(
                'required' => 1,
                'valueis' => '9',
                'filters' => 'trim',
                'msg' => '验证码类型错误'
        ),
        'captcha'=>array(
                'required' => 1,
                'filters' => 'trim',
                'regex' => '/^\d{4}$/',
                'msg' => '请输入正确的４位验证码'
        ),
         'passwd' =>passwd(),
         'repwd' => confirmpass(),
);

return $rules;
