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
    public static $aliases = array('@dee' => DEE_PATH);
    public static $includePaths = array();
    public static $imports = array();

    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * 
     * @param mixed $type
     * @return mixed
     */
    public static function createObject($type, $params = array())
    {
        
    }

    /**
     * 
     * @param string $alias
     * @return string
     */
    public static function getAlias($alias)
    {
        if ($alias[0] !== '@') {
            return $alias;
        }
        foreach (static::$aliases as $key => $value) {
            if (strpos($alias, $key . '/') === 0) {
                return realpath($value . substr($alias, strlen($key)));
            }
        }
        return false;
    }

    public static function setAlias($alias, $path)
    {
        if ($alias[0] !== '@') {
            $alias = '@' . $alias;
        }
        if ($path === null) {
            unset(static::$aliases[$alias]);
        } else {
            static::$aliases[$alias] = static::getAlias($path);
        }
        krsort(static::$aliases);
    }

    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            include static::$classMap[$className];
        } else {
            if (strpos($className, '\\') === false) {  // class without namespace
                foreach (static::$includePaths as $path) {
                    $fileName = static::getAlias($path) . DIRECTORY_SEPARATOR . $className . '.php';
                    if (is_file($fileName)) {
                        include $fileName;
                    }
                }
            } else {
                $namespace = str_replace('\\', '/', ltrim($className, '\\'));
                $fileName = static::getAlias('@' . $namespace) . '.php';
                if (is_file($fileName)) {
                    include $fileName;
                }
            }
            return class_exists($className, false) || interface_exists($className, false);
        }
        return true;
    }

    public static function import($alias, $forceInclude = false)
    {
        if (isset(static::$imports[$alias])) {
            return static::$imports[$alias];
        }
        if (class_exists($alias, false) || interface_exists($alias, false)) {
            return static::$imports[$alias] = $alias;
        }
        if (($pos = strrpos($alias, '/')) === false) {
            
        }
    }
}
Dee::$classMap = require(DEE_PATH . '/classes.php');
