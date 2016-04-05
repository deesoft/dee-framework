<?php

namespace dee\db;

use Dee;
use dee\base\BaseObject;

/**
 * Description of Connection
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Connection extends BaseObject
{
    /**
     * @var string the Data Source Name, or DSN, contains the information required to connect to the database.
     * Please refer to the [PHP manual](http://www.php.net/manual/en/function.PDO-construct.php) on
     * the format of the DSN string.
     * @see charset
     */
    public $dsn;
    /**
     * @var string the username for establishing DB connection. Defaults to `null` meaning no username to use.
     */
    public $username;
    /**
     * @var string the password for establishing DB connection. Defaults to `null` meaning no password to use.
     */
    public $password;
    /**
     * @var array PDO attributes (name => value) that should be set when calling [[open()]]
     * to establish a DB connection. Please refer to the
     * [PHP manual](http://www.php.net/manual/en/function.PDO-setAttribute.php) for
     * details about available attributes.
     */
    public $attributes;
    /**
     * @var PDO the PHP PDO instance associated with this DB connection.
     * This property is mainly managed by [[open()]] and [[close()]] methods.
     * When a DB connection is active, this property will represent a PDO instance;
     * otherwise, it will be null.
     */
    private $_pdo;
    /**
     * @var string Custom PDO wrapper class.
     */
    public $pdoClass;

    public function open()
    {
        if ($this->_pdo === null) {
            $pdoClass = $this->pdoClass? : 'PDO';
            $dsn = $this->dsn;
            if (strncmp('sqlite:@', $dsn, 8) === 0) {
                $dsn = 'sqlite:' . Dee::getAlias(substr($dsn, 7));
            }
            $this->_pdo = new $pdoClass($dsn, $this->username, $this->password, $this->attributes);
        }
    }

    public function getPdo()
    {
        $this->open();
        return $this->_pdo;
    }
}
