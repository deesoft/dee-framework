<?php

namespace dee\core;

use Dee;

/**
 * Description of Request
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class Request extends Object
{
    private $_cookies = [];
    public $routeVar = 'r';
    public $enablePrettyUrl = false;
    public $cookieValidationKey;
    public $parsers = [];

    public function init()
    {
        if ($this->cookieValidationKey === null) {
            $this->cookieValidationKey = Helper::getRandomKey('request.validationKey');
        }
        foreach ($_COOKIE as $name => $value) {
            if (is_string($value) && $data = Helper::validateData($value, $this->cookieValidationKey) !== false) {
                $this->_cookies[$name] = $data;
            }
        }
    }

    public function resolve()
    {
        if ($this->enablePrettyUrl) {
            return [isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '', $this->get()];
        } else {
            return [$this->get($this->routeVar, ''), $this->get()];
        }
    }

    private $_bodyParams;
    public function post($name = null, $default = null)
    {
        if($this->_bodyParams === null){
            
        }
        if ($name === null) {
            return $_POST;
        } else {
            return isset($_POST[$name]) ? $_POST[$name] : $default;
        }
    }

    public function get($name = null, $default = null)
    {
        if ($name === null) {
            return $_GET;
        } else {
            return isset($_GET[$name]) ? $_GET[$name] : $default;
        }
    }

    public function getCookie($name, $default = null)
    {
        return array_key_exists($this->_cookies, $name) ? $this->_cookies[$name] : $default;
    }
}