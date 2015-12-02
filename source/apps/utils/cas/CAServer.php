<?php 
namespace Ucenter\Utils\cas;
include 'Cookie.php';

class CAServer extends \Phalcon\Mvc\Application
{
    public function __construct()
    {
        $this->cookie = new \Cookie($this->di);
    }

    public function setCookieTGC($tgt, $st, $expire = 0)
    {
        $tgc = array('tgt' => $tgt, 'st' => $st);
        return $this->cookie->set('cas', json_encode($tgc), $expire, true);
    }

    public function getCookieTGC()
    {
        $sTGC = $this->cookie->get('cas', true);
        return json_decode($sTGC, true);
    }
    
    public function createTGT($uid, $mobile, $name)
    {
        return $tgt = array('uid' => $uid, 'umobi' => $mobile, 'uname' => $name);
    }

    public function encrypt($siteId, $value)
    {
        $siteCfg = include 'siteConfig.php';

        $encKey = isset($siteCfg['site'][$siteId]['encKey']) ? $siteCfg['site'][$siteId]['encKey'] : false ;
        return $encKey ? \Encrypt::auth($value, $encKey, 'ENCODE') : $value;
    }

    /**
     * 生成st
     * @param  [type] $siteid [description]
     * @return [type]         [description]
     */
    public function getST($siteId)
    {
        return 'ST-' . $siteId . '-' . self::randomStr();
    }

    public function getSiteIdByST($st)
    {
        $stExp = explode('-', $st);
        return $stExp['1'];
    }

    public static function randomStr($maxRandomLength = 6)
    {
        $PRINTABLE_CHARACTERS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $output = '';   
        for($i=0;$i<$maxRandomLength;$i++)
        {
            $output .= $PRINTABLE_CHARACTERS[mt_rand(0,strlen($PRINTABLE_CHARACTERS)-1)];
        }
        return $output;
    }

    public function logout()
    {
        include 'Curl.php';
        $siteCfg = include 'siteConfig.php';
        $syncLogout = true;
        foreach ($siteCfg['site'] as $siteId => $site)
        {
            if (empty($site['logoutSer']))
            {
                continue;
            }
            if (isset($_COOKIE[$siteId])) {
                $syncLogout = \Curl::send($site['logoutSer'], array('logoutRequest' => true, 'sessid' => $_COOKIE[$siteId]), 'post') == 1 ? true : false;
                $this->cookie->del($siteId);
            }
        }
        return $syncLogout && $this->cookie->del('cas');
    }

    public function getConfig()
    {
        return include 'siteConfig.php';
    }

    public function casSave($siteId, $casTime = false)
    {
        $siteId = $siteId ?: 0;
        $casTime = $casTime ?: 0;

        if(!$st = $this->getST($siteId))
            return false;

        if(!$tgt = $this->createTGT($this->session->get('uid'), $this->session->get('uinfo')['mobile'], $this->session->get('uinfo')['name']))
            return false;

        if(!$tgc = $this->setCookieTGC($tgt, $st, $casTime))
            return false;

        $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();

        return $redis->setex($st, 86400, $tgt) ? true : false;
    }

    public function setRedisUserInfo($uid = '', $uinfo = array())
    {
        $uid = $uid ?: $this->session->get('uid');
        $uinfo = $uinfo ?: $this->session->get('uinfo');

        if ($uid && $uinfo)
        {
            $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
            $redis = $RedisLib::getRedis();
            return $redis->set('ucenter_cas:' . $uid, json_encode($uinfo));
        }
        else
            return false;
    }

    public function delRedisUserInfo($uid)
    {
        $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();
        return $redis->del('ucenter_cas:' . $this->session->get('uid'));
    }
}