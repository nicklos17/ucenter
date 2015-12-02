<?php
namespace Ucenter\Mobile\Controllers;

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

    public function init($obj)
    {
        $mobileObj = new $obj();
        $mobileObj->initMobile($this->validFlag, $this->warnMsg, $this->_sanReq);
        return $mobileObj;
    }

    public function beforeExecuteRoute($dispatcher)
    {
        $this->ctrlName = $this->dispatcher->getControllerName();
        $this->actName = $this->dispatcher->getActionName();

        // 获取校验规则
        $rulesFile = __DIR__ . '/../config/rules/' . $this->ctrlName . 'Rules.php';
        $rules = file_exists($rulesFile) ? include $rulesFile : false;
        $actionRules = $rules && isset($rules[$this->actName]) ? $rules[$this->actName] : false;

        if (!$rules || !$actionRules)
        {
            $this->_sanReq = $_REQUEST;
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
}