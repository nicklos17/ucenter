<?php

/**
 * Services are globally registered in this file
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\DI\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Loader;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * Registering a router
 */
$di['router'] = function() use($di)
{

    $router = new Router();

    if(!isMobile() || (!empty($_GET['v'])&&$_GET['v'] == 'p'))
    {
        $router->setDefaultModule("webpc");
        $di['isMobile'] = function () {return 0;};
    }
    else
    {
        $router->setDefaultModule("mobile");
        $di['isMobile'] = function () {return 1;};
    }

    $router->add("/login", 
        array(
            'controller' => 'index',
            'action' => 'login',
        )
    );

    $router->add("/register", 
        array(
            'controller' => 'index',
            'action' => 'register',
        )
    );

    $router->add("/registersuccess", 
        array(
            'controller' => 'index',
            'action' => 'registersuccess',
        )
    );
    return $router;
};

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di['url'] = function ()
{
    $url = new UrlResolver();
    $url->setBaseUri('/');

    return $url;
};

/**
 * Start the session the first time some component request the session service
 */
$di['session'] = function()
{
    $session = new SessionAdapter();
    $session->start();

    return $session;
};
$loader = new Loader();
$loader->registerNamespaces(array(
    'Ucenter\Mdu' => __DIR__ . '/../apps/mdu'
));
$sysConfig=include __DIR__ . '/../config/sysconfig.php';

$di['sysconfig'] = function () use ($sysConfig) 
{
    return $sysConfig;
};

$loader->register();

    /**
     * [isMobile user_agent判断是否手机访问]
     * @return boolean [description]
     */
    function isMobile()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT']))
        {
            return false;
        }
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $isMobile = false;
        foreach ($mobile_agents as $device)
        {
            if(stristr($user_agent, $device))
            {
                $isMobile = true;
                break;
            }
        }
        return $isMobile;
    }


