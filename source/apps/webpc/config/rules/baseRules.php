<?php
function email()
{
    return array(
        'required' => 1,
        'expect' => 'email',
        'filters' => 'trim',
        'msg' => '请输入正确的邮箱',
    );
}

//选填邮箱设置
function optionalEmail()
{
    return array(
        'required' => 0,
        'expect' => 'email',
        'filters' => 'trim',
        'msg' => '请输入正确的邮箱',
    );
}

function mobile()
{
    return array(
            'required' => 1,
            'length' => 11,
            'filters' => 'trim',
            'regex' => '/^1[3,4,5,7,8]+\\d{9}$/',
            'msg' => '请输入正确的手机号码'
    );
}

function passwd()
{
    return array(
            'required' => 1,
            'length' => array(6, 20),
            'filters' => 'trim',
            'msg' => '密码错误'
       );
}

function confirmpass()
{
    return array(
            'required' => 1,
            'equalTo' => 'passwd',
            'filters' => 'trim',
            'msg' => '请再次输入密码'
        );
}


