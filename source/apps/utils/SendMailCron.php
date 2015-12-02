<?php
use  Ucenter\Mdu\ServiceModule as Service;
use  Ucenter\Mdu\UserModule as User;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;

spl_autoload_register(function ($class) {
    $matches = explode("\\", $class);
    if (file_exists(__DIR__ . '/../mdu/Models/' . $matches[count($matches) - 1] . '.php')) {
        require_once __DIR__ . '/../mdu/Models/' . $matches[count($matches) - 1] . '.php';
    } elseif (file_exists(__DIR__ . '/../utils/' . $matches[count($matches) - 1] . '.php')) {
        require_once __DIR__ . '/../utils/' . $matches[count($matches) - 1] . '.php';
    }
});

class SendMailCron
{
    protected $application = null;
    protected $di = null;

    public function setDi() {
        $this->di = new FactoryDefault();
        
        $this->di['sysconfig'] = function ()
        {
            return require __DIR__ . '/../../config/sysconfig.php';
        };
        return $this->di;
    }

    public function __construct()
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
    }

    /**
     * [sendMailAction 队列发送邮件]
     * @return [type] [description]
     */
    public function sendMail()
    {
        $RedisLib = new \Ucenter\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();
        $num = $redis->lSize('mail_verify');
        if($num >0)
        {
            for($i = 0; $i < $num; $i++)
            {
                //邮件模板需要的数据
                $data = $redis->rPop('mail_verify');
                //得到邮件模板内容
                $emailTplPath = __DIR__ . '/../webpc/views/Template/email.html';
                $emailTpl = file_get_contents($emailTplPath);
                $content = str_replace('{mail_verify_code}',$data['mail_verify_code'], $emailTpl);
                $content = str_replace('{domain}', $this->di['sysconfig']['domain'], $content);
                $content = str_replace('{siteUrl}', $this->di['sysconfig']['siteUrl'], $content);
                $nowtime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
                $config = $this->di['sysconfig'] ;
                $email = new \Ucenter\Utils\Email();
                $email->initialize($this->di['sysconfig']['emailConf']);
                $email->subject($this->di['sysconfig']['mail_subject']);
                $email->from($this->di['sysconfig']['emailFrom'], $this->di['sysconfig']['emailName']);
                $email->to($data['mail_address']);
                $email->message($content);

                if(!$email->send())
                {
                    echo $nowtime, ':', $data['mail_address'], ':邮件发送失败', "\n";
                }
                else
                {
                    echo $nowtime, ':', $data['mail_address'], ':邮件发送成功', "\n";
                }
                //echo $email->print_debugger();
            }
        }
        exit();
    }
}
