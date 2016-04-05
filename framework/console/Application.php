<?php

namespace dee\console;

use Dee;

/**
 * Description of Application
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Application extends \dee\base\Application
{
    const OPTION_APPCONFIG = 'appconfig';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        if (!empty($_SERVER['argv'])) {
            $option = '--' . self::OPTION_APPCONFIG . '=';
            foreach ($_SERVER['argv'] as $param) {
                if (strpos($param, $option) !== false) {
                    $path = substr($param, strlen($option));
                    if (!empty($path) && is_file($file = Dee::getAlias($path))) {
                        $config = require($file);
                    } else {
                        exit("The configuration file does not exist: $path\n");
                    }
                    break;
                }
            }
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => 'dee\console\Request'],
        ]);
    }
}
