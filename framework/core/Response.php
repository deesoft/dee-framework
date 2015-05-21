<?php

namespace dee\core;

use Dee;

/**
 * Description of Response
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Response extends Object
{
    public $headers = [];
    private $_cookies = [];
    public $data;

    public function send()
    {
        $this->sendHeader();
        echo $this->data;
    }

    protected function sendHeader()
    {
        if (headers_sent()) {
            return;
        }
        foreach ($this->headers as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            // set replace for first occurance of header but false afterwards to allow multiple
            $replace = true;
            foreach ($values as $value) {
                header("$name: $value", $replace);
                $replace = false;
            }
        }
        $this->sendCookie();
    }

    protected function sendCookie()
    {
        $key = Dee::$app->request->cookieValidationKey;
        foreach ($this->_cookies as $name => $value) {
            list($value, $expire) = $value;
            $value = Helper::hashData($value, $key);
            setcookie($name, $value, $expire);
        }
    }

    public function addCokie($key, $value, $expire = 0)
    {
        $this->_cookies[$key] = [$value, $expire];
    }
}