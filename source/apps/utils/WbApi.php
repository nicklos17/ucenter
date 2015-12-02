<?php

namespace Ucenter\Utils;

use  \Phalcon\Acl\Exception as E;

class WbApi
{
    const GET_AUTH_CODE_URL = "https://api.weibo.com/oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "https://api.weibo.com/oauth2/access_token";

    public $host = 'https://api.weibo.com/2/';
    public $timeout = 30;
    public $connecttimeout = 30;
    public $format = 'json';
    public $decode_json = TRUE;
    public $http_info;
    public $useragent = 'Sae T OAuth2 v0.1';
    public static $boundary = '';
    public $ssl_verifypeer = FALSE;

    function __construct( $di,$access_token = NULL, $refresh_token = NULL )
    {
        $this->di=$di;
        $config=$this->di->get( 'sdkconfig' ) ;
        $this->client_id=$config['WB']['appid'];
        $this->client_secret=$config['WB']['appkey'];
        $this->callback_url= $this->di['sysconfig']['domain'].$config['WB']['callback'] ;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }

    /**
     * authorize接口
     *
     * 对应API：{@link http://open.weibo.com/wiki/Oauth2/authorize Oauth2/authorize}
     *
     * @param string $url 授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写canvas page的地址
     * @param string $response_type 支持的值包括 code 和token 默认值为code
     * @param string $state 用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
     * @param string $display 授权页面类型 可选范围: 
     *  - default       默认授权页面      
     *  - mobile        支持html5的手机      
     *  - popup         弹窗授权页       
     *  - wap1.2        wap1.2页面        
     *  - wap2.0        wap2.0页面        
     *  - js            js-sdk 专用 授权页面是弹窗，返回结果为js-sdk回掉函数       
     *  - apponweibo    站内应用专用,站内应用不传display参数,并且response_type为token时,默认使用改display.授权后不会返回access_token，只是输出js刷新站内应用父框架
     * @return array
     */
    public function getAuthorizeUrl($response_type = 'code', $state = NULL, $display = NULL)
    {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['redirect_uri'] = $this->callback_url;
        $params['response_type'] = $response_type;
        $params['state'] = $state;
        $params['display'] = $display;
        $login_url= self::GET_AUTH_CODE_URL. "?" . http_build_query($params);
        header( "Location:$login_url" );
    }

    /**
     * access_token接口
     *
     * 对应API：{@link http://open.weibo.com/wiki/OAuth2/access_token OAuth2/access_token}
     *
     * @param string $type 请求的类型,可以为:code, password, token
     * @param array $keys 其他参数：
     *  - 当$type为code时： array('code'=>..., 'redirect_uri'=>...)
     *  - 当$type为password时： array('username'=>..., 'password'=>...)
     *  - 当$type为token时： array('refresh_token'=>...)
     * @return array
     */
    public function getAccessToken($type = 'code')
    {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        if($type === 'token')
        {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } 
        elseif($type === 'code')
        {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $_REQUEST['code'];
            $params['redirect_uri'] = $this->callback_url ;
        } 
        elseif($type === 'password' )
         {
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else 
        {
            throw new E('wrong auth type');
        }
        $response = $this->oAuthRequest(self::GET_ACCESS_TOKEN_URL, 'POST', $params);
        $token = json_decode($response, true);
        if(is_array($token) && !isset($token['error']))
        {
            $this->access_token = $token['access_token'];
            //$this->refresh_token = $token['refresh_token'];
        }
        else
        {
            throw new E('get access token failed.'.$token['error']);
        }
        return $this->access_token;
    }

    /**
     * 格式化url
     * @return string
     * @ignore
     */
    public function oAuthRequest($url, $method, $parameters, $multi = false) 
    {
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }
        switch ($method) {
        case 'GET':
            $url = $url . '?' . http_build_query($parameters);
            return $this->http($url, 'GET');
        default:
            $headers = array();
            if (!$multi && (is_array($parameters) || is_object($parameters)))
            {
                $body = http_build_query($parameters);
            } 
            else 
            {
                $body = self::build_http_query_multi($parameters);
                $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
            }

            return $this->http($url, $method, $body, $headers);
        }
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     */
    function http($url, $method, $postfields = NULL, $headers = array()) 
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        //curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields))
                {
                    $url = "{$url}?{$postfields}";
                }
        }

        if(isset($this->access_token) && $this->access_token )
            $headers[] = "Authorization: OAuth2 ".$this->access_token;

        if(!empty($this->remote_ip) )
        {
            if(defined('SAE_ACCESSKEY') )
            {
                $headers[] = "SaeRemoteIP: " . $this->remote_ip;
            } else {
                $headers[] = "API-RemoteIP: " . $this->remote_ip;
            }
        }
        else
        {
            if(!defined('SAE_ACCESSKEY') )
            {
                $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
            }
        }
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        curl_close ($ci);

        return $response;
    }

    public static function build_http_query_multi($params)
    {
        if (!$params) return '';
        uksort($params, 'strcmp');
        $pairs = array();
        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--'.$boundary;
        $endMPboundary = $MPboundary. '--';
        $multipartbody = '';
        foreach ($params as $parameter => $value) 
        {
            if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) 
            {
                $url = ltrim( $value, '@' );
                $content = file_get_contents( $url );
                $array = explode( '?', basename( $url ) );
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content. "\r\n";
            } 
            else 
            {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value."\r\n";
            }

        }
        $multipartbody .= $endMPboundary;

        return $multipartbody;
    }


    /*
     *封装发送get 请求
     * @return mixed
     */
    public function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json)
        {
            return json_decode($response, true);
        }

        return $response;
    }

    /**
     * 封装发送post请求
     * @return mixed
     */
    public function post($url, $parameters = array(), $multi = false) 
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi );
        if ($this->format === 'json' && $this->decode_json) 
        {
            return json_decode($response, true);
        }
        
        return $response;
    }


    //以下部分可以扩展需要API功能
    
    /**
     * 根据用户UID或昵称获取用户资料
     *
     * 按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博。
     * <br />对应API：{@link http://open.weibo.com/wiki/2/users/show users/show}
     * 
     * @access public
     * @param int  $uid 用户UID。
     * @return array
     */
    public function getUserInfoByUid( $uid )
    {
        $params=array();
        if($uid !== NULL )
        {
            $this->_idFormat($uid);
            $params['uid'] = $uid;
        }

        return $this->get('users/show', $params );
    }

    /**
     * OAuth授权之后，获取授权用户的UID
     *
     * 对应API：{@link http://open.weibo.com/wiki/2/account/get_uid account/get_uid}
     * 
     * @access public
     * @return array
     */
    public function getUid()
    {
        return $this->get( 'account/get_uid' );
    }

    protected function _idFormat(&$id) 
    {
        if(is_float($id) )
        {
            $id = number_format($id, 0, '', '');
        } elseif(is_string($id) ) 
        {
            $id = trim($id);
        }
    }

}