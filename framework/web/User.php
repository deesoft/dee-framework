<?php

namespace dee\web;

use Dee;
use dee\base\BaseObject;

/**
 * Description of User
 *
 * @property string $id
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class User extends BaseObject
{
    public $cookieKey = '__identity';
    public $sessionKey = '__identity';
    public $allowAutoLogin = false;
    public $cookieTimeout = 86400;
    private $_id = false;

    public function getId()
    {
        if ($this->_id === false) {
            if (isset($_SESSION[$this->sessionKey])) {
                return $this->_id = $_SESSION[$this->sessionKey];
            } elseif ($this->allowAutoLogin && ($id = Dee::$app->request->getCookie($this->cookieKey)) !== null) {
                $this->_id = $id;
                return $_SESSION[$this->sessionKey] = $this->_id;
            }
            $this->_id = null;
        }
        return $this->_id;
    }

    public function login($id)
    {
        $_SESSION[$this->sessionKey] = $this->_id = $id;
        if($this->allowAutoLogin){
            Dee::$app->response->addCokie($this->cookieKey, $id, $this->cookieTimeout);
        }
    }

    public function logout()
    {
        unset($_SESSION[$this->sessionKey]);
        if(Dee::$app->request->getCookie($this->cookieKey) !== null){
            Dee::$app->response->addCokie($this->cookieKey, null, 0);
        }
    }
    
    public function getIsGuest()
    {
        return $this->id === null;
    }
}