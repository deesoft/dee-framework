<?php

namespace dee\base;

use Dee;
use Exception;

/**
 * Description of Application
 *
 * @property Request $request
 * @property Response $response
 * @property string $basePath
 * @property string $runtimePath
 * @property string $viewPath
 * @property View $view
 * @property Controller $controller
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Application extends BaseObject
{
    public $defaultRoute = 'site';
    public $controller;
    public $layout = 'main';
    private $_components = [];
    /**
     * @var string the namespace that controller classes are located in.
     * This namespace will be used to load controller classes by prepending it to the controller class name.
     * The default namespace is `app\controllers`.
     *
     * Please refer to the [guide about class autoloading](guide:concept-autoloading.md) for more details.
     */
    public $controllerNamespace = 'app\\controllers';

    public function __construct($config = [])
    {
        Dee::$app = $this;
        $this->preInit($config);

        parent::__construct($config);
    }

    public function preInit(&$config)
    {
        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            throw new Exception('The "basePath" configuration for the Application is required.');
        }

        if (isset($config['runtimePath'])) {
            $this->setRuntimePath($config['runtimePath']);
            unset($config['runtimePath']);
        } else {
            // set "@runtime"
            $this->getRuntimePath();
        }

        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
        $this->registerErrorHandler($config);
    }

    public function registerErrorHandler(&$config)
    {
        if (isset($config['components']['errorHandler'])) {
            $this->set('errorHandler', $config['components']['errorHandler']);
            unset($config['components']['errorHandler']);
            $this->errorHandler->register();
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $psr4file = Dee::getAlias('@vendor/composer/autoload_psr4.php', false);
        if ($psr4file && is_file($psr4file)) {
            foreach (include($psr4file) as $alias => $path) {
                if (!is_array($path)) {
                    Dee::setAlias(str_replace('\\', '/', trim($alias, '\\')), $path);
                }
            }
        }
    }

    public function run()
    {
        $request = $this->request;
        $response = $this->handleRequest($request);
        $response->send();
    }

    /**
     *
     * @param  Request  $request
     * @return Response
     */
    protected function handleRequest($request)
    {
        list($route, $params) = $request->resolve();
        $result = $this->runAction($route, $params);
        if ($result instanceof Response) {
            return $result;
        }
        $response = $this->response;
        if ($result !== null) {
            $response->data = $result;
        }

        return $response;
    }

    public function runAction($route, $params)
    {
        $parts = $this->createController($route);

        if (is_array($parts)) {
            /* @var $controller Controller */
            list($controller, $actionID) = $parts;
            $oldController = $this->controller;
            $this->controller = $controller;
            $result = $controller->runAction($actionID, $params);
            $this->controller = $oldController;

            return $result;
        } else {
            throw new Exception("Unable to resolve the request '$route'.");
        }
    }

    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
        }

        // double slashes or leading/ending slashes may cause substr problem
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }

        if (strpos($route, '/') !== false) {
            list ($id, $route) = explode('/', $route, 2);
        } else {
            $id = $route;
            $route = '';
        }

        if (($pos = strrpos($route, '/')) !== false) {
            $id .= '/' . substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }

        $controller = $this->createControllerByID($id);
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($id . '/' . $route);
            $route = '';
        }

        return $controller === null ? false : [$controller, $route];
    }

    protected function createControllerById($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return null;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return null;
        }

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix) . $className, '\\');

        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        if (is_subclass_of($className, 'dee\base\Controller')) {
            $controller = Dee::createObject($className, [$id]);
            return get_class($controller) === $className ? $controller : null;
        } elseif (DEE_DEBUG) {
            throw new Exception('Controller class must extend from dee\base\Controller.');
        } else {
            return null;
        }
    }

    public function get($name, $throwException = true)
    {
        if (isset($this->_components[$name])) {
            if (!is_object($this->_components[$name])) {
                $this->_components[$name] = Dee::createObject($this->_components[$name]);
            }

            return $this->_components[$name];
        } elseif ($throwException) {
            throw new Exception("Unknown component ID: $name");
        } else {
            return null;
        }
    }

    public function has($name)
    {
        return isset($this->_components[$name]);
    }

    public function __get($name)
    {
        if (isset($this->_components[$name])) {
            return $this->get($name);
        } else {
            return parent::__get($name);
        }
    }

    public function set($name, $value)
    {
        $this->_components[$name] = $value;
    }

    public function setComponents($values)
    {
        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function getComponents($loaded = false)
    {
        $result = [];
        foreach ($this->_components as $name => $value) {
            if (!$loaded || is_object($value)) {
                $result[$name] = $value;
            }
        }

        return $result;
    }
    /**
     *
     * @var string
     */
    private $_basePath;

    public function getBasePath()
    {
        return $this->_basePath;
    }

    public function setBasePath($path)
    {
        $path = Dee::getAlias($path);
        $p = realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->_basePath = $p;
            Dee::setAlias('@app', $p);
        } else {
            throw new Exception("The directory does not exist: $path");
        }
    }
    /**
     *
     * @var string
     */
    private $_runtimePath;

    public function getRuntimePath()
    {

        if ($this->_runtimePath === null) {
            $this->_runtimePath = $this->basePath . '/runtime';
        }

        return $this->_runtimePath;
    }

    public function setRuntimePath($path)
    {
        $this->_runtimePath = Dee::getAlias($path);
    }
    private $_vendorPath;

    /**
     * Returns the directory that stores vendor files.
     * @return string the directory that stores vendor files.
     * Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * @param string $path the directory that stores vendor files.
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Dee::getAlias($path);
        Dee::setAlias('@vendor', $this->_vendorPath);
        Dee::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower');
        Dee::setAlias('@npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'npm');
    }
    /**
     *
     * @var string
     */
    private $_viewPath;

    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->basePath . '/views';
        }

        return $this->_viewPath;
    }

    public function setViewPath($path)
    {
        $this->_viewPath = Dee::getAlias($path);
    }
    private $_layoutPath;

    /**
     * Returns the directory that contains layout view files for this module.
     * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
     */
    public function getLayoutPath()
    {
        if ($this->_layoutPath !== null) {
            return $this->_layoutPath;
        } else {
            return $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
        }
    }

    /**
     * Sets the directory that contains the layout files.
     * @param string $path the root directory or path alias of layout files.
     * @throws InvalidParamException if the directory is invalid
     */
    public function setLayoutPath($path)
    {
        $this->_layoutPath = Dee::getAlias($path);
    }

    protected function coreComponents()
    {
        return [
            'view' => ['class' => 'dee\base\View'],
            'db' => ['class' => 'dee\db\Connection'],
            'errorHandler' => ['class' => 'dee\base\ErrorHandler'],
            'response' => ['class' => 'dee\base\Response'],
        ];
    }

    /**
     *
     * @return View
     */
    public function getView()
    {
        return $this->get('view');
    }

    /**
     *
     * @return \dee\db\Connection
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     *
     * @return ErrorHandler
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }
}
