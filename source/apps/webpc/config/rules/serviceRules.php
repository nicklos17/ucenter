<?php
include __DIR__.'/baseRules.php';

$rules['sendMsg'] = array(
        '_request' => array('soap', 'secure', 'ajax'),
        '_method' => array(
            'post' => array('mobile','type'),
        ),
      'mobile' => array(
            'required' => '',
            'length' => 11,
            'filters' => 'trim',
            'regex' => '/^1[3,4,5,7,8]+\\d{9}$/',
            'msg' => '请输入正确的手机号码'
   		 ),
        'type' => array(
            'required' => 1,
            'range' => array(1, 7,9,11),
            'filters' => 'trim',
            'msg' => '请选择类型',
        ),

);
$rules['sendMail'] = array(
        '_request' => array('soap', 'secure', 'ajax'),
        '_method' => array(
            'post' => array('email'),
        ),

        'email' => array(
            'required' => '',
            'filters' => 'trim',
            'regex' => '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/',
            'msg' => '请输入正确的邮箱'
        ),


);

return $rules;
