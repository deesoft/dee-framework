<?php

/**
 * Description of DResponse
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DResponse extends DObject
{
    public $headers = array();
    private $_cookies = array();
    public $data;

    public function send()
    {
        echo $this->data;
        $this->sendHeader();
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
        foreach ($this->_cookies as $name => $value) {
            list($value, $expire) = $value;
            setcookie($name, $value, $expire);
        }
    }

    public function addCokie($key, $value, $expire = 0)
    {
        $this->_cookies[$key] = array($value, $expire);
    }
}