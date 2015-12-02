<?php
class Encrypt
{
    public static function auth($string, $key = '', $operation = 'ENCODE', $expiry = 0)
    {
        $key_length = 4;
        $fixedkey = md5($key);
        $egiskeys = md5(substr($fixedkey, 16, 16));
        $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
        $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
        $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys), 0, 16) . $string : base64_decode(strtr(substr($string, $key_length), '-_', '+/'));

        if($operation == 'ENCODE')
        {
            $string .= substr(md5(microtime(true)), -4);
        }
        if(function_exists('mcrypt_encrypt') == true)
        {
            $result= self::_sys_auth_ex($string, $fixedkey, $operation);
        }
        else
        {
            $i = 0; $result = '';
            $string_length = strlen($string);
            for ($i = 0; $i < $string_length; $i++){
                $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
            }
        }
        if($operation == 'DECODE')
        {
            $result = substr($result, 0,-4);
        }
        
        if($operation == 'ENCODE')
        {
            return $runtokey . rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
        }
        else
        {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$egiskeys), 0, 16))
            {
                return substr($result, 26);
            }
            else
            {
                return '';
            }
        }
    }

    /**
    * 字符串加密、解密扩展函数
    *
    *
    * @param    string  $txt        字符串
    * @param    string  $operation  ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
    * @param    string  $key        密钥：数字、字母、下划线
    * @return   string
    */
    protected static function _sys_auth_ex($string, $key, $operation = 'ENCODE') 
    { 
        $encrypted_data = "";
        $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
     
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $key = substr($key, 0, mcrypt_enc_get_key_size($td));
        mcrypt_generic_init($td, $key, $iv);

        if($operation == 'ENCODE')
        {
            $encrypted_data = mcrypt_generic($td, $string);
        }
        else
        {
            $encrypted_data = rtrim(mdecrypt_generic($td, $string));
        }
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $encrypted_data;
    }
}