<?php

namespace app\controllers;

use dee\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'hello';

    public function actionHello()
    {
        return 'hello world';
    }
}
