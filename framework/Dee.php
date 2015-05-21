<?php
require(__DIR__ . '/BaseDee.php');

/**
 * Description of Dee
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Dee extends \dee\BaseDee
{
    
}
Dee::$classMap = require(__DIR__ . '/classes.php');
spl_autoload_register(['Dee', 'autoload']);
