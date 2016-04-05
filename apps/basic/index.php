<?php

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/deesoft/dee/Dee.php');

$config = require(__DIR__.'/protected/config/main.php');

$app = new dee\core\Application($config);

$app->run();
