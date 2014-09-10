<?php

/**
 * Description of DHelper
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DHelper
{

    public static function getRandomKey($key, $generate = false)
    {
        $file = Dee::$app->runtimePath . '/key.json';
        if (is_file($file)) {
            $content = json_decode(file_get_contents($file), true);
        } else {
            $content = array();
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
            $hash = md5(serialize(array(
                $key,
                __CLASS__,
                $raw)));

            if ($hash == $parity) {
                return $raw;
            }
        }
        return false;
    }

    public static function hashData($data, $key)
    {
        $hash = md5(serialize(array(
            $key,
            __CLASS__,
            $data)));
        return base64_encode(serialize(array($hash, $data)));
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
}