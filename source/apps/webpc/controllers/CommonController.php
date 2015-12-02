<?php

namespace Ucenter\Webpc\Controllers;
use Ucenter\Utils\cas\CAServer;
class CommonController extends ControllerBase
{
    public function miniHeaderAction()
    {
        $cas = new CAServer();
        $casConfig = $cas->getConfig();
        $loginParam = '';
        if(isset($_SERVER['HTTP_REFERER']) && $backurl = $_SERVER['HTTP_REFERER']){
            $refer = parse_url($backurl);
            $auth = 0;
            $siteId = '';
            foreach ($casConfig['site'] as $site => $siteInfo) {
                $casDomain = parse_url($siteInfo['domain']);
                if ($refer['host'] == $casDomain['host']) {
                    $auth = 1;
                    $siteId = '&siteid=' . $site;
                    break;
                }
            }
            $loginParam = '?backurl=' . urlencode($backurl) . $siteId . '&auth=' . $auth;
        }

        $this->view->pick('common/miniHeader');
        $this->view->setVars(array('loginParam' => $loginParam));
        $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT);
    }
}