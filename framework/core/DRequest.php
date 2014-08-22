<?php

/**
 * Description of DRequest
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DRequest extends DObject
{

    public function resolve()
    {
        
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
}