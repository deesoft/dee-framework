<?php
define('MDM_PATH', dirname(__FILE__));

/**
 * Description of Mdm
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class Mdm
{
    /**
     *
     * @var MApplication 
     */
    public static $app;
    
    /**
     *
     * @var array 
     */
    public static $classMap;
}

Mdm::$classMap = require(MDM_PATH.'/classes.php');