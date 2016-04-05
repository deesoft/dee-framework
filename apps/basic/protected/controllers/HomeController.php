<?php

namespace app\controllers;

/**
 * Description of HomeController
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class HomeController extends \dee\core\Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHallo()
    {
        return 'Hallo World!';
    }
}