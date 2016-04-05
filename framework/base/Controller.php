<?php

namespace dee\base;

use Dee;
use ReflectionMethod;
use Exception;

/**
 * Description of Controller
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Controller extends BaseObject
{
    public $id;
    public $defaultAction = 'index';
    public $actionParams = [];
    public $layout = 'main';

    public function __construct($id, $config = [])
    {
        $this->id = $id;
        parent::__construct($config);
    }

    public function runAction($id, $params = [])
    {
        if (empty($id)) {
            $id = $this->defaultAction;
        }
        if (!empty($params)) {
            if (isset($params['_aliases'])) {
                foreach ($this->optionAliases() as $alias => $name) {
                    if (array_key_exists($alias, $params['_aliases'])) {
                        $params[$name] = $params['_aliases'][$alias];
                    }
                }
                unset($params['_aliases']);
            }
            foreach ($this->options($id) as $name) {
                if (array_key_exists($name, $params)) {
                    $default = $this->$name;
                    $value = $params[$name];
                    if (is_array($default)) {
                        $this->$name = preg_split('/(?!\(\d+)\s*,\s*(?!\d+\))/', $value);
                    } elseif ($default !== null) {
                        settype($value, gettype($default));
                        $this->$name = $value;
                    } else {
                        $this->$name = $value;
                    }
                    unset($params[$name]);
                }
            }
        }
        if (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
            if (method_exists($this, $methodName)) {
                $method = new ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return $this->runWithParams($method, $params);
                }
            }
        }
        $route = $this->id . '/' . $id;
        throw new Exception("Unable to resolve the request '$route'.");
    }

    public function options($id)
    {
        return[];
    }

    public function optionAliases()
    {
        return[];
    }

    /**
     *
     * @param ReflectionMethod $method
     * @param array $params
     */
    public function runWithParams($method, $params = [])
    {
        $args = [];
        $missing = [];
        $actionParams = [];

        foreach ($method->getParameters() as $i => $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                $args[] = $actionParams[$name] = $param->isArray() ? (array) $params[$name] : $params[$name];
                unset($params[$name]);
            } elseif (array_key_exists($i, $params)) {
                $args[] = $actionParams[$name] = $param->isArray() ? preg_split('/\s*,\s*/', $params[$i]) : $params[$i];
                unset($params[$i]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        $this->actionParams = $actionParams;

        return call_user_func_array([$this, $method->getName()], $args);
    }

    public function render($view, $params = [])
    {
        $output = $this->getView()->render($view, $params, $this);
        $layoutFile = $this->findLayoutFile();
        if ($layoutFile !== false) {
            return $this->getView()->renderFile($layoutFile, ['content' => $output], $this);
        } else {
            return $output;
        }
    }

    public function renderPartial($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    public function getViewPath()
    {
        return Dee::$app->getViewPath() . '/' . $this->id;
    }
    private $_view;

    /**
     *
     * @return View
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = Dee::$app->view;
        }
        return $this->_view;
    }

    public function setView($value)
    {
        $this->_view = $value;
    }

    /**
     *
     */
    public function findLayoutFile()
    {
        $app = Dee::$app;
        if (is_string($this->layout)) {
            $layout = $this->layout;
        } elseif ($this->layout === null) {
            $layout = $app->layout;
        }

        if (empty($layout)) {
            return false;
        }
        if (strncmp($layout, '@', 1) === 0) {
            $file = Dee::getAlias($layout);
        } else {
            $file = $app->getLayoutPath() . DIRECTORY_SEPARATOR . trim($layout, '/');
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        return $file . '.php';
    }
}
