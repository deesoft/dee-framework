<?php

/**
 * Description of DResponse
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DResponse extends DObject
{
    public $headers = array();
    public $cokies = array();
    public $data;

    public function send()
    {
        
    }

    public function addCokie($key, $value, $expire=0)
    {
        $this->cokies[$key] = array($value, $expire);
    }
}