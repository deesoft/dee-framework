<?php

/**
 * Description of DController
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DController extends DObject
{
    public $id;
    public $defaultAction = 'index';
    public $actionParams = array();

    public function __construct($id, $config = array())
    {
        $this->id = $id;
        parent::__construct($config);
    }

    public function runAction($id, $params = array())
    {
        if (empty($id)) {
            $id = $this->defaultAction;
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

    /**
     * 
     * @param ReflectionMethod $method
     * @param array $params
     */
    public function runWithParams($method, $params = array())
    {
        $args = array();
        $missing = array();
        $actionParams = array();
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = is_array($params[$name]) ? $params[$name] : array($params[$name]);
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new Exception("Invalid data received for parameter '{$name}'.");
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new Exception(strtr('Missing required parameters: {params}', array(
                'params' => implode(', ', $missing),
            )));
        }
        $this->actionParams = $actionParams;
        return call_user_func_array(array($this, $method->getName()), $args);
    }
}