<?php

namespace dee\web;

/**
 * Description of Application
 *
 * @property Session $session
 * @property Request $request
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Application
{

    /**
     * @inheritdoc
     */
    protected function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => 'dee\web\Request'],
            'session' => ['class' => 'dee\web\Session'],
        ]);
    }
}
