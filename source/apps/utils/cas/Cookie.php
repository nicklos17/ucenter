<?php
include 'Encrypt.php';

class Cookie
{
    public function __construct($di)
    {
        $this->di = $di;
        $siteCfg = include 'siteConfig.php';
        $this->encCode = $siteCfg['auth_key'] ?: '';
    }
    
    public function set($name, $value, $time = 0, $enc = true, $encKey = '')
    {
        if ($enc)
        {
            $encKey = $encKey ?: $this->encCode;
            $enValue = Encrypt::auth($value, $encKey);
        }
        else
        {
            $enValue = $value;
        }

        return setcookie($name, $enValue, $time, '/');
    }

    public function get($name, $dec = true, $encKey = '')
    {
        $enValue = isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
        if (!$enValue)
        {
            return false;
        }
        if ($dec)
        {
            $encKey = $encKey ?: $this->encCode;
            $deValue = Encrypt::auth($enValue, $encKey, 'DECODE');
        }
        else
        {
            $deValue = $value;
        }
        return $deValue ?: false;
    }

    public function del($name)
    {
        return $this->set($name, '', time()-100);
    }
}