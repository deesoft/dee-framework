<?php

require(dirname(__FILE__).'/../framework/Mdm.php');

$config = require(dirname(__FILE__).'/config/main.php');

$app = new MApplication($config);
$app->run();