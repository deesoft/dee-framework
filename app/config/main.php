<?php

return array(
    'basePath' => dirname(dirname(__FILE__)),
    'defaultRoute' => 'home',
    'components' => array(
        'db' => array(
            'dsn' => 'mysql:host=localhost;dbname=mydb',
            'username' => '',
            'password' => ''
        )
    )
);
