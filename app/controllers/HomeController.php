<?php

/**
 * Description of HomeController
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class HomeController extends DController
{
    public function actionIndex()
    {
        return 'Hello World!';
    }
    
    public function actionView()
    {
        return $this->render('test');
    }
}