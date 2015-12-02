<?php
namespace Ucenter\Webpc\Controllers;

use Phalcon\Mvc\Controller,
     Ucenter\Utils;

class ControllerBase extends Controller
{
    protected $warnMsg = false;     // 校验错误提示信息，包括code和msg
    protected $warnMsgCode = false;     // 校验错误提示信息，仅包括code
    protected $warnMsgMsg = false;     // 校验错误提示信息，仅包括msg
    protected $validFlag = true;    // 校验结果标识  true - 通过   false - 拒绝
    protected $_sanReq = array();   // 经处理过的参数
    protected $ctrlName;    // 当前访问控制器名
    protected $actName;    // 当前访问方法名

    public function beforeExecuteRoute($dispatcher)
    {
        $this->authCheck();

        $this->ctrlName = $this->dispatcher->getControllerName();
        $this->actName = $this->dispatcher->getActionName();

        // 获取校验规则
        $rulesFile = __DIR__ . '/../config/rules/' . $this->ctrlName . 'Rules.php';
        $rules = file_exists($rulesFile) ? include $rulesFile : false;
        $actionRules = $rules && isset($rules[$this->actName]) ? $rules[$this->actName] : false;

        if (!$rules || !$actionRules)
        {
            $this->_sanReq = $this->request->get();
            return true;
        }

        $utils = new Utils\RulesParse($actionRules);
        $utils->parse();

        if (!$utils->resFlag)
        {
            $this->validFlag = false;
            $this->warnMsg = $utils->warnMsg;
            $this->warnMsgCode = $utils->warnMsgCode;
            $this->warnMsgMsg = $utils->warnMsgMsg;
        }
        else
        {
            $this->_sanReq = $utils->_sanReq;
        }
    }

    protected function showMsg($url, $error, $title, $errCode)
    {
       $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT);
       $this->view->setVars(array(
            'url' => $url,
            'error'=>$error,
            'title'=>$title,
            'errCode' => $errCode
          ));
        $this->dispatcher->forward(array(
            'controller' => 'public', 'action' => 'error')
        );
    }

    public function initMobile($validFlag, $warnMsg, $_sanReq)
    {
        $this->validFlag = $validFlag;
        $this->warnMsg = $warnMsg;
        $this->_sanReq = $_sanReq;
    }

    /**
     * 校验用户登录状态，session和cas信息均需存在
     * @return [type] [description]
     */
    public function authCheck()
    {
        if(empty($this->session->get('uid')))
        {
            $key = substr(md5($this->di['sysconfig']['siteUrl']), 5, 15);
            if ($this->cookies->has($key))
            {
                $val = explode(':', base64_decode($this->cookies->get($key)));
                $uid = base64_decode($val[1]);
                $lastTime = base64_decode($val[2]);
                $keepTime = $val[3];
                $nowTime = $_SERVER['REQUEST_TIME'];
                $user = new \Ucenter\Mdu\UserModule();
                $userInfo = $user->getUserInfo($uid);
                if($lastTime == $userInfo['u_last_logintime'] && $val[0] === substr(md5($userInfo['u_mobi']), 8, 20))
                {
                    if($user->updateUserLoginTime($userInfo['u_mobi'], $nowTime))
                    {
                        $val = base64_encode(substr(md5($userInfo['u_mobi']), 8, 20).':'.base64_encode($uid).':'.base64_encode($nowTime).':'.$keepTime);
                        setcookie(substr(md5($this->di['sysconfig']['siteUrl']), 5, 15), $val, $keepTime, '/');
                    }

                    $this->session->set('uid', $userInfo['u_id']);
                    $this->session->set('uinfo', array('name' => $userInfo['u_name'], 'mobile' => $userInfo['u_mobi']));
                }
            }
        }
            
        if (($this->session->has('uid') && !isset($_COOKIE['cas'])) || (!$this->session->has('uid') && isset($_COOKIE['cas'])))
         {
            $this->response->redirect('index/logout');
        }
    }
}