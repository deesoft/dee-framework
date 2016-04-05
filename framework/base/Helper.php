<?php

namespace dee\base;

use Dee;

/**
 * Description of DHelper
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Helper
{

    /**
     *
     * @param type $key
     * @param type $generate
     * @return type
     */
    public static function getRandomKey($key, $generate = false)
    {
        $file = Dee::$app->runtimePath . '/key.json';
        if (is_file($file)) {
            $content = json_decode(file_get_contents($file), true);
        } else {
            $content = [];
        }
        if ($generate || !isset($content[$key])) {
            $content[$key] = md5(__FILE__ . time());
            file_put_contents($file, json_encode($content));
        }
        return $content[$key];
    }

    public static function validateData($data, $key)
    {
        if ($data = @base64_decode($data) !== false && $data = @unserialize($data) !== false) {
            list($parity, $raw) = $data;
            $hash = md5(serialize([$key, __CLASS__, $raw]));
            if ($hash == $parity) {
                return $raw;
            }
        }
        return false;
    }

    public static function hashData($data, $key)
    {
        $hash = md5(serialize([$key, __CLASS__, $data]));
        return base64_encode(serialize([$hash, $data]));
    }

    /**
     * Creates a new directory.
     *
     * This method is similar to the PHP `mkdir()` function except that
     * it uses `chmod()` to set the permission of the created directory
     * in order to avoid the impact of the `umask` setting.
     *
     * @param string $path path of the directory to be created.
     * @param integer $mode the permission to be set for the created directory.
     * @param boolean $recursive whether to create parent directories if they do not exist.
     * @return boolean whether the directory is created successfully
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir)) {
            self::createDirectory($parentDir, $mode, true);
        }
        $result = mkdir($path, $mode);
        chmod($path, $mode);
        return $result;
    }

    /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     * arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    if (isset($res[$k])) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::arrayMerge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    public static function url($route = [], $schema = false)
    {
        
    }
}
