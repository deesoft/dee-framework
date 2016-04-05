<?php

namespace dee\base;

use Dee;

/**
 * Description of Request
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
abstract class Request extends BaseObject
{
    private $_isConsole;

    abstract public function resolve();

    /**
     * Get is console
     * @return boolean
     */
    public function getIsConsole()
    {
        return $this->_isConsole === null ? PHP_SAPI === 'cli' : $this->_isConsole;
    }

    /**
     * Set is console
     * @param boolean $value
     */
    public function setIsConsole($value)
    {
        $this->_isConsole = $value;
    }
}
