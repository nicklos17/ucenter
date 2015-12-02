<?php
    return [
        'QQ'=>[
            'appid'=>'101133869',
            'appkey'=>'9beb3fdc51532e94f7e2d9da1be3176b',
            'callback'=>'/oauth/qqCallBack'
        ],

        'WB'=>[
            'appid'=>'3290034852',
            'appkey'=>'325b459e076d566883541c0319b30f4f',
            'callback'=>'/oauth/sinaCallback'
        ],
        'WX'=>[
            'appid'=>'wx18872d68b1789bd7',
            'appkey'=>'51f28750f7b2db53d5688785799d2756',
            'callback'=>'/oauth/wxCallback'
        ],
        'Alipay' => [
            // 加密key，开通支付宝账户后给予
            'key' => 'dh1ryftz705dl4mt75oq8y3eflhsp1wf',
            // 合作者ID，支付宝有该配置，开通易宝账户后给予
            'partner' => '2088311599513065',
            //字符编码格式 目前支持 gbk 或 utf-8
            'sign_type' => strtoupper('MD5'), 
            'input_charset' => strtolower('utf-8'),
            //ca证书路径地址，用于curl中ssl校验 请保证cacert.pem文件在当前文件夹目录中
            'cacert' =>getcwd().'\\cacert.pem',
            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            'transport' => 'http',
            'callback'=>'/oauth/alipayCallback'
        ]
    ];
