<?php
namespace rpcService;

require_once dirname(dirname(__FILE__)) . '/utils/Thrift/ClassLoader/ThriftClassLoader.php';
use Thrift\ClassLoader\ThriftClassLoader;

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', dirname(dirname(__FILE__)) . '/utils');
$loader->registerDefinition('thriftrpc', dirname(__FILE__) . '/gen-php');
$loader->register();

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TBufferedTransport;
use Thrift\Exception\TException;

class RpcService {
   public function __construct($serverIP, $serverPort, $server = false)
    {
        if(!$server)// 启动client
        {
            $this->socket = new TSocket($serverIP, $serverPort);
        }
        else // 启动服务器
        {
            
        }

        $this->trans = new TBufferedTransport($this->socket, 1024, 1024);
        $this->protocol = new TBinaryProtocol($this->trans);
        $this->client = new \thriftrpc\ThriftRpcClient($this->protocol);

        $this->trans->open();
    }

    public function smsSend($mobile, $msg)
    {
        $this->client->SmsSend($mobile, $msg);
    }

    public function setDevHalt($devId, $mobi)
    {
        $this->client->SetDevHalt($devId, $mobi);
    }

    public function setDevMod($devId, $mobi, $mod)
    {
        $this->client->SetDevMod($devId, $mobi, $mod);
    }

    public function getDevices($uid)
    {
        return $this->client->GetUserDevices($uid);
    }

	public function __destruct()
	{
		$this->trans->close();
	}
}

// $a = new \rpcservice\RpcService();
//$a->smsSend('13959268510', 'hello world');
