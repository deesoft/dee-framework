<?php

defined('DEE_DEBUG') or define('DEE_DEBUG', false);

require(__DIR__ . '/protected/vendor/deesoft/dee/Dee.php');

$config = [
    'id' => 'benchmark',
    'basePath' => __DIR__ . '/protected',
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
        ],
    ],
];

$application = new dee\web\Application($config);
$application->run();
