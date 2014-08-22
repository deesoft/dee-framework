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
        return [
            'a'=>['a','b',3],
            'b'=>2,
            3
        ];
    }
}