<?php

namespace dee\base;

use Dee;

/**
 * Description of BaseObject
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BaseObject implements Configurable
{
    use ObjectTrait;
    /**
     * 
     * @param array $config
     */
    public function __construct($config = [])
    {
        Dee::configure($this, $config);
        $this->init();
    }

    /**
     * Initialize object
     */
    public function init()
    {

    }

}