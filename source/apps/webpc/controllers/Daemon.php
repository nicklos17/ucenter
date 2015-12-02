<?php 
set_time_limit(0);

/**
 * 启动swoole服务端, 常驻进程
 */
function runSwooleServer()
{
    require_once __DIR__ . '/../../utils/SwooleUserServer.php';
    $swooleServer = new SwooleUserServer('0.0.0.0', 9507, 8, 0, 2); //同时开启8个，0表示非守护进程运行, 2表示固定模式投递客户端请求
    $swooleServer->main();
}

runSwooleServer();
?>