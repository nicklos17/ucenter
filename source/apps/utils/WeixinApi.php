<?php

namespace Ucenter\Utils;

use  \Phalcon\Acl\Exception as E;

class WeixinApi
{
    protected $connBase = 'https://open.weixin.qq.com/connect/qrconnect?';
    protected $acctokenBase = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
    protected $apiBase = 'https://api.weixin.qq.com/';

    public function __construct($di)
    {
        $config=$di->get('sdkconfig');
        define('WX_ID', $config['WX']['appid']);
        define('WX_SECRET', $config['WX']['appkey']);
        define('WX_CALLBACK', urlencode($di->get('sysconfig')['domain'] . $config['WX']['callback']));
    }

    /**
     * 获取登录授权接口
     * @return [type] [description]
     */
    public function loginUrl()
    {
        $loginUrl = $this->connBase;
        $loginUrl .= 'appid=' . WX_ID;
        $loginUrl .= '&redirect_uri=' . WX_CALLBACK;
        $loginUrl .= '&response_type=code&scope=snsapi_login&state=cloud';

        return $loginUrl;
    }

    /**
     * 根据code值获取access token
     * @param  [string] [code] [description]
     * @param  [string] [state] [description]
     * @return [type] [description]
     */
    public function getAccessToken($code, $state)
    {
        $accUrl = $this->acctokenBase;
        $accUrl .= 'appid=' . WX_ID;
        $accUrl .= '&secret=' . WX_SECRET;
        $accUrl .= '&code=' . $code;
        $accUrl .= '&grant_type=authorization_code';

        return \Ucenter\Utils\Curl::send($accUrl);
    }

    /**
     * 获取用户信息
     * @param  [type] $accessToken [description]
     * @return [type]              [description]
     */
    public function getUserInfo($accessToken, $openId)
    {
        $apiUrl = $this->apiBase . 'sns/userinfo?';
        $apiUrl .= 'access_token=' . $accessToken;
        $apiUrl .= '&openid=' . $openId;

        return \Ucenter\Utils\Curl::send($apiUrl);
    }
}