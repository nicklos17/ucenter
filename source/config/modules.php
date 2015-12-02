<?php

/**
 * Register application modules
 */
$application->registerModules(array(
    'webpc' => array(
        'className' => 'Ucenter\Webpc\Module',
        'path' => __DIR__ . '/../apps/webpc/Module.php'
    ),

    'mobile' => array(
        'className' => 'Ucenter\Mobile\Module',
        'path' => __DIR__ . '/../apps/mobile/Module.php'
    ),
));
