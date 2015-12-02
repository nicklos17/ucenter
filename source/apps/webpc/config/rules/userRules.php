<?php

include __DIR__.'/baseRules.php';

$rules['editName'] = array(
        '_request' => array('ajax'),
        '_method' => array(
            'post' => array('name'),
        ),
        'name' => array(
                'required' => 1,
                'length' => array(1, 12),
                'filters' => 'trim',
                'msg' => '请输入正确的昵称'
        ),

);

$rules['editEmail'] = array(
        '_request' => array('ajax'),
        '_method' => array(
            'post' => array('email')
        ),
        'email' => email()
);

$rules['editPasswd'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('regtype','captcha','passwd','repwd','codeImg')
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
        'codeImg'=>array(
            'required' => '',
            'filters' => 'trim',
            'length' => 4,
            'msg' => '请输入正确的４位验证码'
        ),
         'passwd' =>passwd(),
         'repwd' => confirmpass(),
);

$rules['accountunbind'] = array(
        '_request' => array('ajax'),
        '_method' => array(
                'post' => array('type')
        ),
        'type'=>array(
            'required' => 1,
            'length' => array(2,10),
            'filters' => 'trim'
        )
);

$rules['cropPhoto'] = array(
        '_request' => array('soap', 'secure'),
        '_method' => array(
                'post' => array('x1', 'y1', 'x2', 'y2', 'w', 'h')
        ),
        'x1'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d+$/',
        ),
        'y1'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d+$/',
        ),
        'x2'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d+$/',
        ),
        'y2'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d+$/',
        ),
        'w'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d+$/',
        ),
        'h'=>array(
            'required' => 1,
            'filters' => 'trim',
            'regex' => '/^\d+$/',
        )
);

return $rules;
