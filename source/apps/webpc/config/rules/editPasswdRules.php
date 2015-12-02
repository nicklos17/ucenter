<?php
include __DIR__.'/baseRules.php';

$rules['editPasswd'] = array(
        '_request' => array('ajax'),
        '_method' => array(
            // 'post' => array('mobile', 'smscaptach', 'passwd', 'confirmpass', 'readme', 'email'),
            'get' => array('mobile','type'),
            // 'cookie' => array('xxxx'),
        ),

        'mobile' => array(
            'required' => 1,
            'length' => 11,
            'filters' => 'trim',
            'regex' => '/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/',
            'msg' => '请输入正确的手机号码'
        ),
        'type' => array(
            'required' => 1,
            'range' => array(1, 7,9,11),
            'filters' => 'trim',
            'msg' => '请选择类型',
        ),

);


return $rules;
