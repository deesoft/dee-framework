<?php

namespace dee\console;

/**
 * Description of Request
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Request extends \dee\base\Request
{
    private $_params;

    public function resolve()
    {
        $rawParams = $this->getParams();
        if (isset($rawParams[0])) {
            $route = $rawParams[0];
            array_shift($rawParams);
        } else {
            $route = '';
        }

        $params = [];
        foreach ($rawParams as $param) {
            if (preg_match('/^--(\w+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                if ($name !== Application::OPTION_APPCONFIG) {
                    $params[$name] = isset($matches[2]) ? $matches[2] : true;
                }
            } elseif (preg_match('/^-(\w+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                $params['_aliases'][$name] = isset($matches[2]) ? $matches[2] : true;
            } else {
                $params[] = $param;
            }
        }
        return [$route, $params];
    }

    public function getParams()
    {
        if ($this->_params === null) {
            if (isset($_SERVER['argv'])) {
                $this->_params = $_SERVER['argv'];
                array_shift($this->_params);
            } else {
                $this->_params = [];
            }
        }
        return $this->_params;
    }

    public function setParams($value)
    {
        $this->_params = $value;
    }
}
