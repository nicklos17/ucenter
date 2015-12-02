<?php

namespace Ucenter\Utils;

class Inputs
{
    /**
     * 密码加密算法
     */
    public static function makeSecert($pass, $salt, $counts = 1000, $length = 32)
    {
        return hash_pbkdf2("sha256", $pass, $salt, $counts, $length);
    }
    
    /**
     * 生成随机码
     * @param string $length 验证码位数
     * @param string $chars
     * @return string
     */
    public static function random($length, $chars = '0123456789')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++)
        {
        $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
    /**
     * 创建目录
     * @param str $path 多级目录
     * @param int $mode 权限级别
     * @return boolean
     */
    public static function createdir($path, $mode = 0777)
    {
        if(!is_dir($path))
        {
            //true为可创建多级目录
            $re = mkdir($path, $mode, true);
            if($re)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return TRUE;
        }
    }
}
