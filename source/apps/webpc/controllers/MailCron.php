<?php 
set_time_limit(0);

function runSendMail()
{
    require_once __DIR__ . '/../../utils/SendMailCron.php';
    $cron = new SendMailCron();
    $cron->sendMail();
}

runSendMail();
?>