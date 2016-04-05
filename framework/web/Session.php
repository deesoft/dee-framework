<?php

namespace dee\web;

use Dee;
use dee\base\BaseObject;

/**
 * Description of Session
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Session extends BaseObject
{
    public $handler;
    public $customeStorage = false;
    public $sessionPath = '@runtime/session';

    /**
     * @inheritdoc
     */
    public function init()
    {
        register_shutdown_function([$this, 'close']);

        if ($this->handler !== null) {
            if (!is_object($this->handler)) {
                $this->handler = Dee::createObject($this->handler);
            }
            @session_set_save_handler($this->handler, false);
        } elseif ($this->customeStorage) {
            $this->sessionPath = Dee::getAlias($this->sessionPath);
            @session_set_save_handler(
                [$this, 'openSession'],
                [$this, 'closeSession'],
                [$this, 'readSession'],
                [$this, 'writeSession'],
                [$this, 'destroySession'],
                [$this, 'gcSession']
            );
        }
        session_start();
    }

    /**
     * Ends the current session and store session data.
     */
    public function close()
    {
        if ($this->getIsActive()) {
            @session_write_close();
        }
    }

    /**
     * Frees all session variables and destroys all data registered to a session.
     */
    public function destroy()
    {
        if ($this->getIsActive()) {
            @session_unset();
            $sessionId = session_id();
            @session_destroy();
            @session_id($sessionId);
        }
    }

    /**
     * @return boolean whether the session has started
     */
    public function getIsActive()
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }

    /**
     * Session open handler.
     * This method should be overridden if [[useCustomStorage]] returns true.
     * Do not call this method directly.
     * @param string $savePath session save path
     * @param string $sessionName session name
     * @return boolean whether session is opened successfully
     */
    public function openSession($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Session close handler.
     * This method should be overridden if [[useCustomStorage]] returns true.
     * Do not call this method directly.
     * @return boolean whether session is closed successfully
     */
    public function closeSession()
    {
        return true;
    }

    protected function getFile($id)
    {
        $id = md5(__CLASS__ . $id);
        return $this->sessionPath . '/' . substr($id, 0, 2) . '/' . substr($id, 2, 1) . '/' . $id;
    }

    /**
     * Session read handler.
     * This method should be overridden if [[useCustomStorage]] returns true.
     * Do not call this method directly.
     * @param string $id session ID
     * @return string the session data
     */
    public function readSession($id)
    {
        $file = $this->getFile($id);
        return (is_file($file)) ? file_get_contents($file) : '';
    }

    /**
     * Session write handler.
     * This method should be overridden if [[useCustomStorage]] returns true.
     * Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id, $data)
    {
        $file = $this->getFile($id);
        if (file_put_contents($file, $data)) {
            $time = (int) ini_get('session.gc_maxlifetime') + time();
            touch($file, $time);
        }
    }

    /**
     * Session destroy handler.
     * This method should be overridden if [[useCustomStorage]] returns true.
     * Do not call this method directly.
     * @param string $id session ID
     * @return boolean whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        $file = $this->getFile($id);
        return unlink($file);
    }

    /**
     * Session GC (garbage collection) handler.
     * This method should be overridden if [[useCustomStorage]] returns true.
     * Do not call this method directly.
     * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
     * @return boolean whether session is GCed successfully
     */
    public function gcSession($maxLifetime)
    {
        $this->gcRecursive($this->sessionPath);
        return true;
    }

    /**
     * Recursively removing expired session files under a directory.
     * This method is mainly used by [[gc()]].
     * @param string $path the directory under which expired cache files are removed.
     * under `$path` will be removed.
     */
    protected function gcRecursive($path)
    {
        if (($handle = opendir($path)) !== false) {
            while (($file = readdir($handle)) !== false) {
                if ($file[0] === '.') {
                    continue;
                }
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {
                    $this->gcRecursive($fullPath);
                } elseif (filemtime($fullPath) < time()) {
                    @unlink($fullPath);
                }
            }
            closedir($handle);
        }
    }
}
