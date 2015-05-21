<?php

namespace dee\core;

use Dee;
use Exception;

/**
 * Description of View
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class View extends Object
{
    private $_viewFiles = [];
    public $params = [];
    public $context;
    public $title = '';

    public function render($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);
        return $this->renderFile($viewFile, $params, $context);
    }

    /**
     *
     * @param string $view
     * @param Controller $context
     * @return string
     * @throws Exception
     */
    protected function findViewFile($view, $context = null)
    {
        if (strncmp($view, '@', 1) === 0) {
            // e.g. "@app/views/main"
            $file = Dee::getAlias($view);
        } elseif (strncmp($view, '/', 1) === 0) {
            // e.g. "//layouts/main"
            $file = Dee::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
        } elseif ($context !== null) {
            $file = $context->getViewPath() . DIRECTORY_SEPARATOR . $view;
        } elseif (($currentView = $this->getViewFile()) !== false) {
            $file = dirname($currentView) . DIRECTORY_SEPARATOR . $view;
        } else {
            throw new Exception("Invalid render view {$view}");
        }
        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        return $file . '.php';
    }

    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Dee::getAlias($viewFile);
        $oldContext = $this->context;
        if ($context !== null) {
            $this->context = $context;
        }
        $this->_viewFiles[] = dirname($viewFile);
        $output = $this->renderInternal($viewFile, $params);
        $this->context = $oldContext;
        array_pop($this->_viewFiles);
        return $output;
    }

    public function renderInternal($_viewFile_, $_params_ = [])
    {
        extract($_params_, EXTR_PREFIX_SAME, 'data');
        ob_start();
        ob_implicit_flush(false);
        require($_viewFile_);
        return ob_get_clean();
    }

    public function getViewFile()
    {
        return end($this->_viewFiles);
    }
}