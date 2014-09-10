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
     * @param mixed $config
     * @return mixed
     */
    public static function createObject($config, $params = array())
    {
        if (is_string($config)) {
            $type = $config;
            $config = array();
        } elseif (isset($config['class'])) {
            $type = $config['class'];
            unset($config['class']);
        } else {
            throw new Exception('Object configuration must be an array containing a "class" element.');
        }
        if (!class_exists($type, false)) {
            $type = static::import($type, true);
        }


        $reflection = new ReflectionClass($type);

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            $i = 0;
            foreach ($constructor->getParameters() as $param) {
                if (!isset($params[$i])) {
                    if ($param->isDefaultValueAvailable()) {
                        $params[$i] = $param->getDefaultValue();
                    } else {
                        throw new Exception("Missing required parameter \"{$param->getName()}\" when instantiating \"{$type}\".");
                    }
                }
                $i++;
            }
            $params[] = $config;
            $object = $reflection->newInstanceArgs($params);
        } else {
            $object = new $type();
        }
        return $object;
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
        if (class_exists($alias, false) || interface_exists($alias, false)) {
            return static::$imports[$alias] = $alias;
        }
        if (isset(static::$imports[$alias])) {
            return static::$imports[$alias];
        }
        if (($pos = strrpos($alias, '/')) !== false) {
            $class = substr($alias, $pos + 1);
            if ($class === '*') {
                static::$includePaths[] = static::getAlias('@' . substr($alias, 0, $pos));
                static::$includePaths = array_unique(static::$includePaths);
            } else {
                $path = static::getAlias('@' . $alias);
                static::$classMap[$class] = $path;
                if ($forceInclude) {
                    include $path;
                }
                return static::$imports[$alias] = $class;
            }
        } else {
            return $alias;
        }
        return false;
    }
}
Dee::$classMap = require(DEE_PATH . '/classes.php');
spl_autoload_register(array('Dee', 'autoload'));
