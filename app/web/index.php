<?php

require(dirname(dirname(__DIR__)).'/framework/Dee.php');

$config = require(dirname(__DIR__).'/config/main.php');

$app = new dee\core\Application($config);

$app->run();
