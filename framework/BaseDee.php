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
     * @var \yii\console\Application|\yii\web\Application the application instance
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
     * @param mixed $config
     * @return mixed
     */
    public static function createObject($config, $params = [])
    {
        if (is_string($config)) {
            $type = $config;
            $config = [];
        } elseif (isset($config['class'])) {
            $type = $config['class'];
            unset($config['class']);
        } else {
            throw new Exception('Object configuration must be an array containing a "class" element.');
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

            if (!empty($config) && $reflection->implementsInterface('dee\core\Configurable')) {
                $params[$i - 1] = $config;
                return $reflection->newInstanceArgs($params);
            } else {
                $object = $reflection->newInstanceArgs($params);
                return static::configure($object, $config);
            }
        } else {
            $object = new $type();
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