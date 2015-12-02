<?php
include __DIR__.'/baseRules.php';

$rules['validemail'] = array(
        '_request' => array('ajax'),
        '_method' => array(
             'get' => array('token'),
        ),

        'token' => array(
            'required' => 1,
            'filters' => 'trim',
            'msg' => 'token错误',
        ),

);
return $rules;
