<?php

return [
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'home',
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=mydb',
            'username' => '',
            'password' => ''
        ]
    ]
];
