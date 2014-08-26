<?php

/**
 * Description of DView
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DView extends DObject
{
    private $_viewPath = array();
    public $params = array();
    public $context;

    public function render($view, $params = array(), $context = null)
    {
        if ($context === null) {
            $currentPath = end($this->_viewPath);
        } else {
            $oldContext = $this->context;
            $this->context = $context;
            $currentPath = $context->getViewPath();
        }
        if ($currentPath !== false) {
            $viewFile = $currentPath . '/' . $view . '.php';
            $output = $this->renderFile($viewFile, $params);
            if (isset($oldContext)) {
                $this->context = $oldContext;
            }
            return $output;
        } else {
            throw new Exception("Invalid render view {$view}");
        }
    }

    public function renderFile($viewFile, $params = array())
    {
        $this->_viewPath[] = dirname($viewFile);
        $output = $this->renderInternal($viewFile, $params);
        array_pop($this->_viewPath);
        return $output;
    }

    public function renderInternal($_viewFile_, $_params_ = array())
    {
        extract($_params_, EXTR_PREFIX_SAME, 'data');
        ob_start();
        ob_implicit_flush(false);
        require($_viewFile_);
        return ob_get_clean();
    }
}