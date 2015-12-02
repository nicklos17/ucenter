<?php

namespace Ucenter\Utils;

class QcApi 
{
    const GET_AUTH_CODE_URL = 'https://graph.qq.com/oauth2.0/authorize';
    const GET_ACCESS_TOKEN_URL = 'https://graph.qq.com/oauth2.0/token';
    const GET_OPENID_URL = 'https://graph.qq.com/oauth2.0/me';

    function __construct($di,$access_token=NULL)
    {
        $this->di=$di;
        $config=$this->di->get('sdkconfig');
        $this->appid=$config['QQ']['appid'];
        $this->appkey=$config['QQ']['appkey'];
        $this->callback_url=urlencode($this->di['sysconfig']['domain'].$config['QQ']['callback']);
        $this->access_token=$access_token;
        $this->errorMsg = array(
            '20001' => '<h2>配置文件损坏或无法读取，请重新执行intall</h2>',
            '30001' => '<h2>The state does not match. You may be a victim of CSRF.</h2>',
            '50001' => '<h2>可能是服务器无法请求https协议</h2>可能未开启curl支持,请尝试开启curl支持，重启web服务器，如果问题仍未解决，请联系我们'
       );
    }

    function loginUrl($scope = '')
    {
        $secure=md5(uniqid(rand(), TRUE));
        session_start();
        $_SESSION['state'] = $secure;
        $params = [
            'client_id' => $this->appid,
            'redirect_uri' => $this->callback_url,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $secure
        ];
        $login_url =  $this->_combineUrl(self::GET_AUTH_CODE_URL, $params); 
            header("Location:$login_url");
    }

    /**
     * [getAccessToken 获取accesstoken]
     * @return [type] [description]
     */
    public function getAccessToken()
    {
            session_start();
            if($_GET['state'] != $_SESSION['state'])
            {
                $this->_showError("30001");
            }
            $keysArr = [
                "grant_type" => "authorization_code",
                "client_id" => $this->appid,
                "redirect_uri" => $this->callback_url,
                "client_secret" => $this->appkey,
                "code" => $_GET['code']
            ];        
             //------构造请求access_token的url
            $token_url = $this->_combineUrl(self::GET_ACCESS_TOKEN_URL, $keysArr);
            $response = $this->_getContents($token_url);
            //判断是否返回错误信息,即没有返回
            if(strpos($response, "callback") !== false)
            {
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
                $msg = json_decode($response);

                if(isset($msg->error))
                {
                    $this->_showError($msg->error, $msg->error_description);
                }
            }
            $params = array();
            parse_str($response, $params);
            $this->access_token = $params['access_token'];

            return $params['access_token'];
        }

        /**
         * [get_openid 获取openid]
         * @return [type] [description]
         */
        
        public function getOpenid()
        {
        
            $keysArr = array(
                'access_token' => $this->access_token
           );

            $graph_url = $this->_combineUrl(self::GET_OPENID_URL, $keysArr);
            $response = $this->_getContents($graph_url);
            //--------检测错误是否发生
            if(strpos($response, "callback") !== false)
            {
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response = substr($response, $lpos + 1, $rpos - $lpos -1);
            }

            $user = json_decode($response);
            if(isset($user->error))
            {
                $this->_showError($user->error, $user->error_description);
            }

            return $user->openid;
     }

    function http($url, $postfields='', $method='GET', $headers=array())
    {
        $ci=curl_init();
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        if($method=='POST')
        {
            curl_setopt($ci, CURLOPT_POST, TRUE);
            if($postfields!='')
                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        }
        $headers[]='User-Agent: qqPHP(piscdong.com)';
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLOPT_URL, $url);
        $response=curl_exec($ci);
        curl_close($ci);

        return $response;
    }

    private  function _combineUrl($baseURL,$keysArr)
    {
            $combined = $baseURL."?";
            $valueArr = array();
            foreach($keysArr as $key => $val){
                $valueArr[] = "$key=$val";
            }
            $keyStr = implode("&",$valueArr);
            $combined .= ($keyStr);
            
            return $combined;
    }

     /**
        * get_contents
        * 服务器通过get请求获得内容
        * @param string $url       请求的url,拼接后的
        * @return string           请求返回的内容
        */
        private function _getContents($url)
        {
            if (ini_get('allow_url_fopen') == '1') 
            {
                $response = file_get_contents($url);
            }
            else
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_URL, $url);
                $response = curl_exec($ch);
                curl_close($ch);
            }
            //-------请求为空
            if(empty($response)){
                $this->_showError('50001');
            }

            return $response;
    }

    /**
    * showError
    * 显示错误信息
    * @param int $code    错误代码
    * @param string $description 描述信息（可选）
       */
    private function _showError($code, $description = '$')
    {   
      
        echo '<meta charset=\"UTF-8\">';
        if($description == '$')
        {
            die($this->errorMsg[$code]);
        }
        else
        {
            echo "<h3>error:</h3>$code";
            echo "<h3>msg  :</h3>$description";
            exit(); 
        }
    }

    /**
        * get
    * get方式请求资源
        * @param string $url     基于的baseUrl
    * @param array $keysArr  参数列表数组      
    * @return string         返回的资源内容
    */

    public function get($url, $keysArr)
    {
        $combined = $this->_combineUrl($url, $keysArr);
        return $this->_getContents($combined);
    }

        /**
        * post
        * post方式请求资源
        * @param string $url       基于的baseUrl
        * @param array $keysArr    请求的参数列表
        * @param int $flag         标志位
        * @return string           返回的资源内容
        */
    
    public function post($url, $keysArr, $flag = 0)
    {
        $ch = curl_init();
        if(! $flag) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keysArr);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);

        return $ret;
    }
}