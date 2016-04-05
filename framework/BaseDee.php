<?php

namespace dee;

use Exception;
use ReflectionClass;

/**
 * Gets the application start timestamp.
 */
defined('DEE_BEGIN_TIME') or define('DEE_BEGIN_TIME', microtime(true));
/**
 * This constant defines the framework installation directory.
 */
defined('DEE_PATH') or define('DEE_PATH', __DIR__);
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('DEE_DEBUG') or define('DEE_DEBUG', false);
/**
 * This constant defines in which environment the application is running. Defaults to 'prod', meaning production environment.
 * You may define this constant in the bootstrap script. The value could be 'prod' (production), 'dev' (development), 'test', 'staging', etc.
 */
defined('DEE_ENV') or define('DEE_ENV', 'prod');
/**
 * Whether the the application is running in production environment
 */
defined('DEE_ENV_PROD') or define('DEE_ENV_PROD', DEE_ENV === 'prod');
/**
 * Whether the the application is running in development environment
 */
defined('DEE_ENV_DEV') or define('DEE_ENV_DEV', DEE_ENV === 'dev');
/**
 * Whether the the application is running in testing environment
 */
defined('DEE_ENV_TEST') or define('DEE_ENV_TEST', DEE_ENV === 'test');

/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('DEE_ENABLE_ERROR_HANDLER') or define('DEE_ENABLE_ERROR_HANDLER', true);

/**
 * Description of BaseDee
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BaseDee
{
    /**
     * @var array class map used by the Yii autoloading mechanism.
     * The array keys are the class names (without leading backslashes), and the array values
     * are the corresponding class file paths (or path aliases). This property mainly affects
     * how [[autoload()]] works.
     * @see autoload()
     */
    public static $classMap = [];
    /**
     * @var \dee\base\Application the application instance
     */
    public static $app;
    /**
     * @var array registered path aliases
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = ['@dee' => __DIR__];

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
    public static function createObject($type, $params = [])
    {
        if (is_string($type)) {
            return static::get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::get($class, $params, $type);
        } elseif (is_callable($type)) {
            return call_user_func($type, $params);
        } elseif (is_array($type)) {
            throw new Exception('Object configuration must be an array containing a "class" element.');
        } else {
            throw new Exception('Unsupported configuration type: ' . gettype($type));
        }
    }
    private static $_definitions = [];

    public static function set($id, $definition = null)
    {
        if ($definition === null) {
            unset(self::$_definitions[$id]);
        } else {
            self::$_definitions[$id] = $definition;
        }
    }

    public static function get($class, $params = [], $config = [])
    {
        if (isset(self::$_definitions[$class])) {
            $definition = self::$_definitions[$class];
            if (is_string($definition)) {
                $type = $definition;
            } elseif (is_array($definition)) {
                $type = isset($definition['class']) ? $definition['class'] : $class;
                unset($definition['class']);
                $config = array_merge($definition, $config);
            } elseif (is_callable($definition)) {
                return call_user_func($definition, $params, $config);
            }
            if ($type !== $class) {
                return static::get($type, $params, $config);
            }
        }

        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            $args = [];
            $i = 0;
            /* @var $param \ReflectionParameter */
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();
                if (($c = $param->getClass()) !== null) {
                    $className = $c->getName();
                    if (isset($params[0]) && $params[0] instanceof $className) {
                        $args[$i] = array_shift($params);
                    } elseif (static::$app->has($name) && ($obj = static::$app->get($name)) instanceof $className) {
                        $args[$i] = $obj;
                    } else {
                        $args[$i] = static::get($className);
                    }
                } elseif (count($params)) {
                    $args[$i] = array_shift($params);
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[$i] = $param->getDefaultValue();
                } elseif (!$param->isOptional()) {
                    throw new Exception("Missing required parameter \"{$name}\" when instantiating \"{$class}\".");
                }
                $i++;
            }

            if (!empty($config) && $reflection->implementsInterface('dee\base\Configurable')) {
                $args[$i - 1] = $config;
                return $reflection->newInstanceArgs($args);
            } else {
                foreach ($params as $value) {
                    $args[] = $value;
                }
                $object = $reflection->newInstanceArgs($args);
                return static::configure($object, $config);
            }
        } else {
            $object = new $class();
            return static::configure($object, $config);
        }
    }

    /**
     *
     * @param string $alias
     * @return string
     */
    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }
        foreach (static::$aliases as $key => $path) {
            if (strpos($alias . '/', $key . '/') === 0) {
                return $path . substr($alias, strlen($key));
            }
        }
        if ($throwException) {
            throw new Exception("Invalid path alias: $alias");
        } else {
            return false;
        }
    }

    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        if ($path === null) {
            unset(static::$aliases[$alias]);
        } else {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            static::$aliases[$alias] = $path;
        }
        krsort(static::$aliases);
    }

    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
            if ($classFile[0] === '@') {
                $classFile = static::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include($classFile);
    }
}
