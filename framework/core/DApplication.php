<?php

/**
 * Description of DApplication
 *
 * @property DRequest $request
 * @property string $basePath
 * @property string $controllerPath
 * 
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DApplication extends DObject
{
    private $_components = [];
    public $defaultRoute = 'site';

    public function __construct($config = array())
    {
        Dee::$app = $this;
        $this->initCoreComponents();
        parent::__construct($config);
    }

    public function run()
    {
        $request = $this->request;
        $this->handleRequest($request);
    }

    /**
     * 
     * @param DRequest $request
     */
    protected function handleRequest($request)
    {
        list($route, $params) = $request->resolve();
        $response = $this->runAction($route, $params);
    }

    public function runAction($route, $params)
    {
        $parts = $this->createController($route);
        if (is_array($parts)) {
            /* @var $controller Controller */
            list($controller, $actionID) = $parts;
            $oldController = Yii::$app->controller;
            Yii::$app->controller = $controller;
            $result = $controller->runAction($actionID, $params);
            Yii::$app->controller = $oldController;

            return $result;
        } else {
            throw new Exception("Unable to resolve the request '$route'.");
        }
    }

    public function createController($route)
    {
        if (empty($route)) {
            $route = $this->defaultRoute;
        }
        $route = trim($route, '/');
        if ($pos = strrpos($route, '/') !== false) {
            $id = substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        } else {
            $id = $route;
            $route = '';
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
        if (!preg_match('%^[a-z0-9\\-_/]+$%', $id)) {
            return null;
        }

        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $fileName = $this->controllerPath . '/' . $prefix . $className . '.php';
        if (is_file($fileName)) {
            include_once($fileName);
        }  else {
            return null;
        }

        if (class_exists($className,false) && is_subclass_of($className, 'DController')) {
            return Dee::createObject($className, [$id]);
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
        if ($this->_basePath === null) {
            throw new Exception('The "basePath" option must be specified.');
        } else {
            return $this->_basePath;
        }
    }

    public function setBasePath($value)
    {
        $this->_basePath = $value;
    }
    /**
     *
     * @var string 
     */
    private $_controllerPath;

    public function getControllerPath()
    {
        if ($this->_controllerPath === null) {
            $this->_controllerPath = $this->basePath . '/controllers';
        }
        return $this->_controllerPath;
    }

    public function setControllerPath($value)
    {
        $this->_controllerPath = $value;
    }

    protected function initCoreComponents()
    {
        $coreComponets = array(
            'request' => array(
                'class' => 'DRequest',
            )
        );
        $this->_components = array_merge_recursive($coreComponets, $this->_components);
    }
}