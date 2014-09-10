<?php

/**
 * Description of DRequest
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DRequest extends DObject
{
    private $_cookies = array();
    public $routeVar = 'r';
    public $urlFormatPath = false;
    public $cookieValidationKey;

    public function init()
    {
        if($this->cookieValidationKey === null){
            $this->cookieValidationKey = DHelper::getRandomKey('request.validationKey');
        }
        foreach ($_COOKIE as $name => $value) {
            if (is_string($value) && $data = DHelper::validateData($value, $this->cookieValidationKey) !== false) {
                $this->_cookies[$name] = $data;
            }
        }
    }

    public function resolve()
    {
        if ($this->urlFormatPath) {
            return array(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '', $this->get());
        } else {
            return array($this->get($this->routeVar, ''), $this->get());
        }
    }

    public function post($name = null, $default = null)
    {
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