<?php

namespace Ucenter\Utils;

use  \Phalcon\Acl\Exception as E;

class AlipayApi
{
    var $alipay_config;
    var $di;
    /**
     *支付宝网关地址（新）
     */
    var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    /**
     * HTTPS形式消息验证地址
     */
    var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     */
    var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    var $sign_type='MD5';

    function __construct($di){
        $this->alipay_config = $di->get('sdkconfig')['Alipay'];
        $this->di = $di;
    }

    public function loginUrl()
    {
        $target_service = "user.auth.quick.login";
        // $return_url = urlencode($this->di->get('sysconfig')['domain'] . $this->alipay_config['callback']);
        $return_url = $this->di->get('sysconfig')['domain'] . $this->alipay_config['callback'];
        $parameter = array(
                "service" => "alipay.auth.authorize",
                "partner" => trim($this->alipay_config['partner']),
                "target_service"    => $target_service,
                "return_url"    => $return_url,
                "anti_phishing_key" => '',
                "exter_invoke_ip"   => '',
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );
        return $this->buildRequestForm($parameter,"get", "确认");
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    function buildRequestMysign($para_sort) {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        
        $mysign = "";
        switch (strtoupper(trim($this->alipay_config['sign_type']))) {
            case "MD5" :
                $mysign = $this->md5Sign($prestr, $this->alipay_config['key']);
                break;
            default :
                $mysign = "";
        }
        
        return $mysign;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    function buildRequestPara($para_temp) {
      //  exit('1');
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);
        
        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
        
        return $para_sort;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
    function buildRequestParaToString($para_temp) {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
        
        //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = createLinkstring($para);
        
        return $request_data;
    }
    
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $button_name) {
//      //待请求参数数组
//      $para = $this->buildRequestPara($para_temp);
        
//      $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->alipay_config['input_charset']))."' method='".$method."'>";
//      while (list ($key, $val) = each ($para)) {
//             $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>\n";
//         }

//      //submit按钮控件请不要含有name属性
//         $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
        
//      //$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
        
//      return $sHtml;

        $para = $this->buildRequestPara($para_temp);
        $mysign=$this->getMysign($para);
        $param['_input_charset']=trim(strtolower($this->alipay_config['input_charset']));
        $param['sign']=$mysign;
        $param['sign_type']=strtoupper(trim($this->sign_type));

        return $this->alipay_gateway_new.http_build_query($para);
    }
    
    function getMysign($param){
        $prestr='';
        foreach($param as $k=>$v)$prestr.=$k.'='.$v.'&';
        $prestr=substr($prestr, 0, count($prestr)-2);
        if(get_magic_quotes_gpc())$prestr=stripslashes($prestr);
        $prestr.=trim($this->alipay_config['key']);
        return md5($prestr);
    }
    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果
     * @param $para_temp 请求参数数组
     * @return 支付宝处理结果
     */
    function buildRequestHttp($para_temp) {
        $sResult = '';
        
        //待请求参数数组字符串
        $request_data = $this->buildRequestPara($para_temp);

        //远程获取数据
        $sResult = getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$request_data,trim(strtolower($this->alipay_config['input_charset'])));

        return $sResult;
    }
    
    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果，带文件上传功能
     * @param $para_temp 请求参数数组
     * @param $file_para_name 文件类型的参数名
     * @param $file_name 文件完整绝对路径
     * @return 支付宝返回处理结果
     */
    function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {
        
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
        $para[$file_para_name] = "@".$file_name;
        
        //远程获取数据
        $sResult = getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$para,trim(strtolower($this->alipay_config['input_charset'])));

        return $sResult;
    }
    
    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
     */
    function query_timestamp() {
        $url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->alipay_config['partner']))."&_input_charset=".trim(strtolower($this->alipay_config['input_charset']));
        $encrypt_key = "";      

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
        
        return $encrypt_key;
    }
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyNotify(){
        if(empty($_POST)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
                
            //写日志记录
            //if ($isSign) {
            //  $isSignStr = 'true';
                //}
                //else {
                //  $isSignStr = 'false';
                //}
                //$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSign.",";
                //$log_text = $log_text.createLinkString($_POST);
                //logResult($log_text);
                    
                //验证
                //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
                //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
                if (preg_match("/true$/i",$responseTxt) && $isSign) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    
        /**
         * 针对return_url验证消息是否是支付宝发出的合法消息
         * @return 验证结果
         */
        function verifyReturn(){
            if(empty($_GET)) {//判断POST来的数组是否为空
                return false;
            }
            else {
                //生成签名结果
                $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
                //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
                $responseTxt = 'true';
                if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}
                    
                //写日志记录
                if ($isSign) {
                    $isSignStr = 'true';
                }
                else {
                    $isSignStr = 'false';
                }
            //  $log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
                // $log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSign.",";
                // echo $log_text;
                // $log_text = $log_text.createLinkString($_GET);
                // logResult($log_text);
                    
                //验证
                //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
                //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
                if (preg_match("/true$/i",$responseTxt) && $isSign) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    
        /**
         * 获取返回时的签名验证结果
         * @param $para_temp 通知返回来的参数数组
         * @param $sign 返回的签名结果
         * @return 签名验证结果
         */
        function getSignVeryfy($para_temp, $sign) {
            //除去待签名参数数组中的空值和签名参数
            $para_filter = $this->paraFilter($para_temp);
    
            //对待签名参数数组排序
            $para_sort = $this->argSort($para_filter);
    
            //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
            $prestr = $this->createLinkstring($para_sort);
    
            // echo '<br /><br />$prestr------<br />'.$prestr.'<br /><br />';
    
            $isSgin = false;
            switch (strtoupper(trim($this->alipay_config['sign_type']))) {
                case "MD5" :
                    $isSgin = $this->md5Verify($prestr, $sign, $this->alipay_config['key']);
                    break;
                default :
                    $isSgin = false;
            }
    
            return $isSgin;
        }
    
        /**
         * 获取远程服务器ATN结果,验证返回URL
         * @param $notify_id 通知校验ID
         * @return 服务器ATN结果
         * 验证结果集：
         * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
         * true 返回正确信息
         * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
         */
        function getResponse($notify_id) {
            $transport = strtolower(trim($this->alipay_config['transport']));
            $partner = trim($this->alipay_config['partner']);
            $veryfy_url = '';
            if($transport == 'https') {
                $veryfy_url = $this->https_verify_url;
            }
            else {
                $veryfy_url = $this->http_verify_url;
            }
            $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
            $responseTxt = $this->getHttpResponse($veryfy_url);
    
            return $responseTxt;
        }

        /**
         * 除去数组中的空值和签名参数
         * @param $para 签名参数组
         * return 去掉空值与签名参数后的新签名参数组
         */
        function paraFilter($para) {
            unset($para['_url']);   // phalcon框架添加
            $para_filter = array();
            foreach($para as $key=>$pa){
                if($key == "sign" || $key == "sign_type" || $pa == "")continue;
                else $para_filter[$key]=$pa;
            }
            return $para_filter;
        }

        /**
         * 对数组排序
         * @param $para 排序前的数组
         * return 排序后的数组
         */
        function argSort($para) {
            ksort($para);
            reset($para);
            return $para;
        }

        /**
         * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
         * @param $para 需要拼接的数组
         * return 拼接完成以后的字符串
         */
        function createLinkstring($para) {
            $arg  = "";
            while (list ($key, $val) = each ($para)) {
                $arg.=$key."=".$val."&";
            }
            //去掉最后一个&字符
            $arg = substr($arg,0,count($arg)-2);

            //如果存在转义字符，那么去掉转义
            if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

            return $arg;
        }

        /**
         * 签名字符串
         * @param $prestr 需要签名的字符串
         * @param $key 私钥
         * return 签名结果
         */
        function md5Sign($prestr, $key) {
            $prestr = $prestr . $key;
            return md5($prestr);
        }

        /**
         * 验证签名
         * @param $prestr 需要签名的字符串
         * @param $sign 签名结果
         * @param $key 私钥
         * return 签名结果
         */
        function md5Verify($prestr, $sign, $key) {
            $prestr = $prestr . $key;
            $mysgin = md5($prestr);

            if($mysgin == $sign) {
                return true;
            }
            else {
                return false;
            }
        }

        /**
         * 远程获取数据
         * 注意：该函数的功能可以用curl来实现和代替。curl需自行编写。
         * $url 指定URL完整路径地址
         * @param $input_charset 编码格式。默认值：空值
         * @param $time_out 超时时间。默认值：60
         * return 远程输出的数据
         */
        function getHttpResponse($url, $input_charset = '', $time_out = "60") {
            $urlarr     = parse_url($url);
            $errno      = "";
            $errstr     = "";
            $transports = "";
            $responseText = "";
            if($urlarr["scheme"] == "https") {
                $transports = "ssl://";
                $urlarr["port"] = "443";
            } else {
                $transports = "tcp://";
                $urlarr["port"] = "80";
            }
            $fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
            if(!$fp) {
                die("ERROR: $errno - $errstr<br />\n");
            } else {
                if (trim($input_charset) == '') {
                    fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
                }
                else {
                    fputs($fp, "POST ".$urlarr["path"].'?_input_charset='.$input_charset." HTTP/1.1\r\n");
                }
                fputs($fp, "Host: ".$urlarr["host"]."\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $urlarr["query"] . "\r\n\r\n");
                while(!feof($fp)) {
                    $responseText .= @fgets($fp, 1024);
                }
                fclose($fp);
                $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");

                return $responseText;
            }
        }
}