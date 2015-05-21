<?php

return [
    'basePath' => dirname(dirname(__FILE__)),
    'defaultRoute' => 'home',
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=mydb',
            'username' => '',
            'password' => ''
        ]
    ]
];
