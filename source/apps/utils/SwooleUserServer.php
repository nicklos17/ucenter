<?php
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\DI\FactoryDefault;
use Phalcon\Loader;
use Ucenter\Mdu\Models\UsersModel;
use Ucenter\Mdu\Models\UserwalletsModel;
use Ucenter\Mdu\Models\CaptchaModel;
use Ucenter\Mdu\Models\EmailVerifyModel;
use Ucenter\Utils\Inputs;

spl_autoload_register(function ($class) {
    $matches = explode("\\", $class);
    if (file_exists(__DIR__ . '/../mdu/Models/' . $matches[count($matches) - 1] . '.php')) {
        require_once __DIR__ . '/../mdu/Models/' . $matches[count($matches) - 1] . '.php';
    } elseif (file_exists(__DIR__ . '/../utils/' . $matches[count($matches) - 1] . '.php')) {
        require_once __DIR__ . '/../utils/' . $matches[count($matches) - 1] . '.php';
    }
});

class SwooleUserServer {
    protected $serv;
    protected $files;

    //protected $tmpPath = '/tmp/';
    protected $host = '0.0.0.0';
    protected $port = 9507;
    protected $works = 8;
    //protected $model;
    //public $debug = true;
    //public $CI;
    protected $logFile = '/tmp/swoole.log';
    protected $hbInterval = 5;
    protected $hbIdle = 10;
    protected $pollNum = 2;
    protected $writerNum = 4;
    protected $maxRequest = 2000;
    protected $dispatch_mode = 2;

    protected $captchamodel = null;
    protected $usermodel = null;
    protected $emailverifymodel = null;
    protected $userwalletmodel = null;

    protected $sdkconfig = null;
    protected $sysconfig = null;

    protected $application = null;
    protected $di = null;

    // 100M
    //static $maxFileSize = 100000000;

    protected $method = array(
        'checkMobi',
        'regFromSwoole',
        'upload',
          'reset',
          'getUserInfo',
      'makeCaptcha',
         'checkCaptcha',
         'capValid',
          'getUserInfoByMobi',
          'isExistEmail',//验证邮件
          'createWallet',
          'coinsInfo',
          'checkUserName',
          'setUserName',
          'updateLevel',
            'checkInReceive',
            'checkThirdLogin',
            'emailverify',//邮件
            'unbindThird',   //第三方解绑
            'bindThird',   //第三方绑定
            'modifyUser',     //更改用户信息
            'sinaOauth',    //新浪授权
            'qqOauth',      //qq授权
            'oauthReg',     //第三方登录补充资料
            'userInfoByIds'  //根据多个亲人的id获取亲人信息
    );

    private function setDi()
    {
        $this->di = new FactoryDefault();

        /**
         * Read configuration
         */
        $config = require __DIR__ . '/../webpc/config/config.php';

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $this->di['db'] = new Mysql(array(
                "host" => $config->database->host,
                "username" => $config->database->username,
                "password" => $config->database->password,
                "dbname" => $config->database->dbname,
                "options" => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                    \PDO::ATTR_CASE => \PDO::CASE_LOWER,
                    \PDO::ATTR_PERSISTENT => false
                )
            ));
           
        //echo "set DI\n";
        return $this->di;
    }

    public function __construct($host='0.0.0.0', $port=9507, $works=8, $daemonize=0, $dispatch_mode = 2, $logFile='')
    {
        $this->host = $host;
        $this->port = $port;
        $this->works = $works;
        $this->daemonize = $daemonize;
        $this->dispatch_mode = $dispatch_mode;
        if($logFile) {
            $this->logFile = $logFile;
        }
    }
    
    /**
     * 用来返回信息
     */
    public function msg($fd, $code, $data)
    {
      if($code != 200)
        {
            $this->serv->send($fd, json_encode(array('flag' => false, 'errCode' => $code, 'errMsg' => $data)));
            $this->serv->close($fd);

        }
        else
        {
            $this->serv->send($fd, json_encode(array('flag' => true, 'data' => $data)));
        }

        return true;
    }

    public function onConnect($serv, $fd, $fromId)
    {
        $this->application = new Application();
        try {
            /**
             * Assign the DI
             */
            $this->application->setDI($this->setDi());
        } catch (Phalcon\Exception $e) {
            echo $e->getMessage();
        } catch (PDOException $e) {
            echo $e->getMessage();  
        }
        
        echo date("Y-m-d H:i:s") . ", new client[$fd] connected.\n";
    }

    public function onReceive(swoole_server $serv, $fd, $fromId, $data)
    {
        //echo "onReceive, worker_id" . $serv->worker_id . "\n";
        /**
         * Handle the request
         */

        $this->sysconfig = require __DIR__ . '/../../config/sysconfig.php';

        $this->sdkconfig = require __DIR__ . '/oauth/sdkconfig.php';

        if (!$this->usermodel) {
            $this->usermodel = new UsersModel();
        }
        if (!$this->captchamodel) {
            $this->captchamodel = new CaptchaModel();
        }
        if (!$this->emailverifymodel) {
            $this->emailverifymodel = new EmailVerifyModel();
        }
        if (!$this->userwalletmodel) {
            $this->userwalletmodel = new UserWalletsModel();
        }

        // 非文件上传
        if(empty($this->files[$fd]))
          {
            $req = json_decode($data, true);
            if($req === false)
                {
                return $this->msg($fd, 400, 'Error Request');
                }
            else if(!in_array($req['cmd'], $this->method))
                {
                return $this->msg($fd, 400, 'Error Method');
                }

            try {
                $res = array();
                echo date("Y-m-d H:i:s") . ", client[$fd], cmd: " . $req['cmd'] . "\n";
                switch($req['cmd'])
                     {
                    case 'checkMobi':// 验证注册手机号
                        $res = $this->checkMobi($req['args']['0']);
                        break;
                    case 'upload': //上传图片
                        $filePath = __DIR__ . '/../../public/' . $req['path'];
                        $file = $req['path'] . '/' . $req['name'];
                        $file_real = $filePath . '/' . $req['name'];
                                //尝试创建目录
                        if(!Inputs::createdir($filePath, 0777))
                                {
                             return $this->msg($fd, 5001, 'can not create catalog.');
                                }
                        $fp = fopen($file_real, 'w');
                        if(!$fp)
                                {
                            return $this->msg($fd, 504, 'can not open file.');
                                }
                        else
                            {
                            $res = 'transmission start';
                            $this->files[$fd] = array('fp' => $fp, 'name' => $file, 'size' => $req['size'], 'recv' => 0);
                                }
                        break;
                    case 'regFromSwoole': //注册
                            $res = $this->regFromSwoole($req['args']['0'], $req['args']['1'], $req['args']['2'], $req['args']['3'], $req['args']['4']);
                            break;
                        case 'makeCaptcha': //发送验证码
                            $res = $this->makeCaptcha($req['args']['0'],$req['args']['1'], $req['args']['2']);
                            break;
                        case 'checkCaptcha': //验证码验证
                            $res = $this->checkCaptcha($req['args']['0'],$req['args']['1'], $req['args']['2'],$req['args']['3']);
                            break;
  
                        case 'capValid': //验证完成
                            $res = $this->capValid($req['args']['0'],$req['args']['1'], $req['args']['2'], $req['args']['3']);
                            break;  
                        case 'getUserInfoByMobi': //根据手机号获取用户信息
                            $res = $this->getUserInfoByMobi($req['args']['0']);
                            break;
                    case 'userInfoByIds': //根据手机号获取用户信息
                            $res = $this->userInfoByIds($req['args']['0']);
                            break;
                        case 'emailverify': //验证完成
                            $res = $this->emailverify($req['args']['0'],$req['args']['1'], $req['args']['2'], $req['args']['3']);
                            break;  
                        case 'createWallet':
                            $res = $this->createWallet($req['args']['0']);
                            break;
                        case 'coinsInfo':
                            $res = $this->coinsInfo($req['args']['0']);
                            break;
                        case 'reset':
                            $res = $this->reset($req['args']['0'], $req['args']['1'], $req['args']['2']);
                            break;
                        case 'checkUserName':
                            $res = $this->checkUserName($req['args']['0'], $req['args']['1']);
                            break;
                        case 'setUserName':
                            $res = $this->setUserName($req['args']['0'], $req['args']['1']);
                            break;
                        case 'isExistEmail':
                            $res = $this->isExistEmail($req['args']['0']);
                            break;
                        case 'updateLevel':
                            $res = $this->updateLevel($req['args']['0']);
                            break;
                        case 'getUserInfo':
                            $res = $this->getUserInfo($req['args']['0']);
                            break;
                        case 'checkInReceive':
                            $res = $this->checkInReceive($req['args']['0'], $req['args']['1']);
                            break;
                        case 'checkThirdLogin':
                            $res = $this->checkThirdLogin($req['args']['0'], $req['args']['1']);
                            break;
                        case 'unbindThird':
                            $res = $this->unbindThird($req['args']['0'], $req['args']['1']);
                            break;
                        case 'bindThird':
                            $res = $this->bindThird($req['args']['0'], $req['args']['1'], $req['args']['2'], $req['args']['3']);
                            break;
                        case 'modifyUser':
                            $res = $this->modifyUser($req['args']['0'], $req['args']['1'], $req['args']['2']);
                            break;
                        case 'sinaOauth':
                            $res = $this->sinaOauth($req['args']['0'], $req['args']['1'], $req['args']['2']); 
                            break;
                        case 'qqOauth':
                            $res = $this->qqOauth($req['args']['0'], $req['args']['1']);
                            break;
                        case 'oauthReg':
                            $res = $this->oauthReg(
                                                        $req['args']['0'],
                                                        $req['args']['1'],
                                                        $req['args']['2'],
                                                    $req['args']['3'],
                                                    $req['args']['4'],
                                                    $req['args']['5'],
                                                    $req['args']['6'],
                                                    $req['args']['7']
                                                            );
                            break;
                     }
                return $this->msg($fd, 200, $res);
            }
            catch(SwooleExcept $e)
            {
                return $this->msg($fd, 503, $e->getMessage());
            }
           }
        else // 文件上传
          {
                $info = & $this->files[$fd];
            $fp = $info['fp'];
            $file = $info['name'];
            if(!fwrite($fp, $data))
                {
                $this->msg($fd, 600, 'fwrite failed. transmission stop.');
                unlink($file);
                unset($this->files[$fd]);
                }
            else
            {
                $info['recv'] += strlen($data);
                if($info['recv'] >= $info['size'])
                     {
                   $this->msg($fd, 200, 'Success, transmission finish. Close connection.');
                    unset($this->files[$fd]);
                     }
            }
         }
    }

    public function onClose($serv, $fd, $fromId)
    {
        //unset($this->files[$fd]);
        // $this->application = null;
        // $this->di = null;
        unset($this->di);
        unset($this->application);
        echo date("Y-m-d H:i:s") . ', client[', $fd, '] closed.', "\n";
    }

    /**
     * 主调用函数
     */
    public function main()
    {
       $serv = new swoole_server($this->host, $this->port);
       $serv->set(array(
            'worker_num' => $this->works,
            'daemonize' => $this->daemonize,
            'log_file' => $this->logFile,
            'heartbeat_check_interval' => $this->hbInterval,
            'heartbeat_idle_time' => $this->hbIdle,
            'reactor_num' => $this->pollNum,
            'writer_num' => $this->writerNum,
            'max_request' => $this->maxRequest,
            'dispatch_mode' => $this->dispatch_mode
        ));

        $serv->on('Start', function($serv){
            echo 'Swoole Server running', "\n";
            swoole_set_process_name('swoole master');
        });

        $serv->on('Timer', array($this, 'onTimer'));
        
        $serv->on('WorkerStart', function ($serv, $worker_id)
        {
            if($worker_id >= $serv->setting['worker_num']) {
                swoole_set_process_name("swoole task worker, worker_id: " . $serv->worker_id);
            } else {
                swoole_set_process_name("swoole event worker, worker_id: " . $serv->worker_id);
            }

            if( $worker_id == 0 ) {
                    $serv->addtimer(3600000);
            }

        });

        $serv->on('ManagerStart', function ($serv)
        {
            swoole_set_process_name("swoole manager worker");
        });

        $serv->on('connect', array($this, 'onConnect'));
        $serv->on('receive', array($this, 'onReceive'));
        $serv->on('close', array($this, 'onClose'));
        $this->serv = $serv;
        $serv->start();
    }
    
    public function onTimer($serv, $interval) {
        $this->sysconfig = require __DIR__ . '/../../config/sysconfig.php';

        $this->sdkconfig = require __DIR__ . '/oauth/sdkconfig.php';

        if (!$this->usermodel) {
            $this->usermodel = new UsersModel();
        }

        $rs = $this->usermodel->pingMysql();
        var_dump($rs);
        echo date("Y-m-d H:i:s") . " miao\n";
    }

    public function makeCaptcha($mobi, $type, $nowtime)
    {
        $captchaInfo = $this->captchamodel->getLastCapthaInfo($mobi, $type);
        if(!empty($captchaInfo))
        {
            //一分钟之内用户只能获取一次验证码
            if($nowtime - $captchaInfo['mc_addtime'] < 60)
            {
                return false;
            }
            //如果已生成的验证码未验证，则生成的验证码为原来的验证码
            if($captchaInfo['mc_validtime'] == 0)
            {
                $captcha = $captchaInfo['mc_captcha'];
            }
            else
            {
                $captcha = $this->random(4);
            }
        }
        else
        {
            $captcha = $this->random(4);
        }
        
        if($this->captchamodel->addCaptcha($mobi, $type, $nowtime, $captcha))
        {
            return $captcha;
        }
        else
        {
            return FALSE;
        }

    }
     
    /**
     * 生成随机码
     * @param string $length 验证码位数
     * @param string $chars
     * @return string
     */
    private function random($length, $chars = '0123456789') {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
/**
     * 判断验证码时效性
     */
    public function checkCaptcha($mobi, $type, $captcha, $nowtime)
    {
        $capInfo = $this->captchamodel->getCapthaTime($mobi, $type, $captcha);
        if(empty($capInfo))
        {
            echo $this->sysconfig['flagMsg']['10011'];
            exit;
        }
        else
        {
            //验证码超过失效
            if($nowtime - $capInfo['mc_addtime'] > $this->sysconfig['capValid'] || $capInfo['mc_validtime'])
            {
                echo $this->sysconfig['flagMsg']['10012'];
                exit;
            }
            return '1'; //验证码有效返回1
        }
    }

    /**
     * 完成验证码的验证
     */
    public function capValid($mobi, $captcha, $type, $nowtime)
    {
        if($this->captchamodel->updateCaptcha($mobi, $captcha, $type, $nowtime) == '0')
        {
            return false;
        }
        else
        {
            return '1';
        }
    }
    /**
     * 新浪授权
     */
    protected function sinaOauth($accessToken, $sinaUid, $ip)
    {
        require_once __DIR__ . '/oauth/saetv2.ex.class.php';
        $oauth = new SaeTClientV2($this->sdkconfig['WB']['WB_AKEY'], $this->sdkconfig['WB']['WB_SKEY'], $accessToken);
        $oauth->set_remote_ip($ip);
        $content = $oauth->show_user_by_id($sinaUid);
        return $content;
    }
    
    /**
     * qq授权
     */
    protected function qqOauth($accessToken, $qqUid)
    {
        require_once __DIR__ . '/oauth/QQAPI/OpenApiV3.php';
        $oauth = new OpenApiV3($this->sdkconfig['QQ']['QQAppID'], $this->sdkconfig['QQ']['QQAppKey']);
        $oauth->setServerName($this->sdkconfig['QQ']['QQServerUrl']);
        $content = $oauth->api('/user/get_user_info', array(
                                                                            'access_token' => $accessToken,
                                                                            'oauth_comsumer_key' => $this->sdkconfig['QQ']['QQAppID'],
                                                                            'openid' => $qqUid, 
                                                                            'format' => 'json'
                                                                            ), 'get', 'https');
        return $content;
    }
    
    /**
     * 检查手机是否注册过
     */
    protected function checkMobi($mobi)
     {
        return $this->usermodel->getUidByMobi($mobi);
     }
     
     /**
      * 注册
      * @param unknown $mobi
      * @param unknown $pass
      * @param unknown $pic
      * @param unknown $regtime
      * @param unknown $email
      */
    protected function regFromSwoole($mobi, $pass, $pic, $email, $regtime)
     {
        return $this->usermodel->regFromSwoole($mobi, Inputs::makeSecert($pass, $regtime), $pic, $email, $regtime);
     }
     /**
      * 邮件
      * Enter description here ...
      * @param unknown_type $mobi
      * @param unknown_type $pass
      * @param unknown_type $pic
      * @param unknown_type $email
      * @param unknown_type $regtime
      */
    protected function emailverify($uid, $email, $secert, $regtime)
     {
        return $this->emailverifymodel->innserEmailVerify($uid, $email, $secert);
     }
     
     /**
      * 第三方登录补充资料
      */
     protected function oauthReg($oauthType, $mobi, $pass, $email, $oauthUid, $nickname, $pic, $regtime)
     {
        return $this->usermodel->oauthReg($oauthType, $mobi, Inputs::makeSecert($pass, $regtime), $email, $oauthUid, $nickname, $pic, $regtime);
     }
     
     /**
      * 根据手机号获取用户信息
      * @param unknown $mobi
      */
    protected function getUserInfoByMobi($mobi)
     {
        return $this->usermodel->getUserInfoByMobi($mobi, null);
     }
     /**
      * 根据Uid获取用户信息
      * @param unknown $uid
      */
    protected function getUserInfo($uid)
     {
        return $this->usermodel->getUserInfo($uid);
     }
     
     /**
      * 创建用户钱包
      */
     protected function createWallet($uid)
     {
        return $this->userwalletmodel->createWallet($uid);
     }
     
     /**
      * 获取用户云币信息
      * @param unknown $uid
      */
    protected function coinsInfo($uid)
     {
        return $this->userwalletmodel->coinsInfo($uid);
     }
     
     /**
      * 亲到发放奖励
      */
    protected function checkInReceive($uid, $coins)
     {
        return $this->userwalletmodel->receive($uid, $coins);
     }
     
     /**
      * 修改密码
      * @param unknown $uid
      * @param unknown $passNew
      */
     protected function reset($mobi, $passNew, $regtime)
     {
        return $this->usermodel->updatePwdByMobi($mobi, Inputs::makeSecert($passNew, $regtime));
     }
     
     /**
      * 检查用户名是否被占用
      * @param unknown $uname
      * @param unknown $uid
      */
     protected function checkUserName($uname, $uid)
     {
        return $this->usermodel->getUserByNick($uname);
     }
     
     /**
      * 设置用户名
      * @param unknown $uname
      * @param unknown $uid
      */
     protected function setUserName($uname, $uid)
     {
        return $this->usermodel->updateNickname($uid, $uname);
     }
     
     /**
      * 升级
      * @param unknown $uname
      * @param unknown $uid
      */
     protected function updateLevel($uid)
     {
        return $this->usermodel->updateLevel($uid);
     }
     
     /**
      * 检查用户是否用第三方登录过
      */
     protected function checkThirdLogin($uTags, $oauthType)
     {
        return $this->usermodel->checkThirdLogin($uTags, $oauthType);
     }
     
     /**
      * 绑定第三方
      */
     protected function bindThird($oauthType, $uid, $uTags, $picUrl)
     {
        return $this->usermodel->oauthBind($oauthType, $uid, $uTags, $picUrl);
     }
     
     
     /**
      * 解绑第三方
      * @param unknown $uTags
      * @param unknown $oauthType
      */
     protected function unbindThird($uid, $oauthType)
     {
        return $this->usermodel->unbindThird($uid, $oauthType);
     }
     
     /**
      * 编辑用户：修改用户名 头像
      * @param unknown $name
      * @param unknown $uid
      * @param string $pic
      */
     protected function modifyUser($name, $uid, $pic)
     {
        return $this->usermodel->modifyUser($name, $uid, $pic);
     }

     protected function isExistEmail($email)
     {
        return $this->usermodel->getEmailByEmail($email);
     }
     /**
      * 根据多个亲人的id获取亲人信息
      * @param unknown $uids
      */
     protected function userInfoByIds($uids)
     {
        return $this->usermodel->userInfoByIds($uids);
     }
     
}