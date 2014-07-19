<?php

define('DEE_PATH', dirname(__FILE__));

/**
 * Description of Dee
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class Dee
{
    /**
     *
     * @var DApplication 
     */
    public static $app;

    /**
     *
     * @var array 
     */
    public static $classMap;
    public static $aliases = ['@dee' => DEE_PATH];

    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }
}
Dee::$classMap = require(DEE_PATH . '/classes.php');
