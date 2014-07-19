<?php

require(dirname(__FILE__).'/../framework/Dee.php');

$config = require(dirname(__FILE__).'/config/main.php');

$app = new DApplication($config);
$app->run();