<?php
namespace Pp\CodeSample;

use Bfc\Core\Filter\RequestFilter;
use \Bfc\Core\View\Template;

/**
 * Class AbstractActionController.
 *
 * @package Pp\CodeSample
 */
class AbstractActionController
{
    /**
     * @var \Bfc\Core\Object\ObjectManager
     * @inject
     */
    public $objectManager;

    /**
     * Absolute path of plugin.
     *
     * @var string $pluginPath
     */
    public $pluginPath = '';

    /**
     * Controller action.
     *
     * @var string
     */
    protected $action = '';

    /**
     * @var \Bfc\Core\Filter\Filter
     * @inject
     */
    protected $requestFilter = null;

    /**
     * Initialize object.
     * Magic method.
     *
     * @return void
     * @throws
     */
    public function initializeObject()
    {
        $this->requestFilter->addFilter(RequestFilter::class);

        $this->pluginPath = realpath(__DIR__ . '/../');
        $action = isset($_REQUEST['action'])
            ? $this->requestFilter->apply($_REQUEST['action'])
            : '';

        $this->setAction($action);
        $this->initializeAction();
    }

    /**
     * Initializes the controller before invoking an action method.
     * Override this method.
     */
    protected function initializeAction()
    {
    }

    /**
     * Default action will be called if no action found.
     * Override this method.
     */
    protected function defaultAction()
    {
    }

    /**
     * Initialize view.
     * Call this method manual to activate action.
     */
    public function initializeView()
    {
        $this->callActionMethod();
    }

    /**
     * Calls the specified action method.
     *
     * @return void
     * @throws \Exception
     */
    protected function callActionMethod()
    {
        $actionMethod = $this->getActionMethod();

        if ($actionMethod && method_exists($this, $actionMethod)) {
            try {
                // call the action method of extends class
                $this->{$actionMethod}();
            } catch (\Exception $exception) {
                throw $exception;
            }
        } else {
            // default method
            $this->defaultAction();
        }
    }

    /**
     * Get action name.
     * The name is in CamelCase format.
     *
     * @return string
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * Set action.
     *
     * @param string $name
     */
    protected function setAction($name)
    {
        $name = trim($name);
        if (strlen($name)) {
            // todo Covert action to smallCase
            $this->action = $name;
        }
    }

    /**
     * Get action method name.
     * The action method should be defined in extends class.
     *
     * @return string
     */
    protected function getActionMethod()
    {
        $actionMethod = '';
        $action = $this->getAction();

        if (strlen($action)) {
            // todo Convert action to CamelCase
            $actionMethod = $action . 'Action';
        }

        return $actionMethod;
    }

    /**
     * Get filtered request parameter.
     *
     * @param string $key The parameter key.
     *
     * @return string
     */
    protected function getRequestParam($key)
    {
        $param = isset($_REQUEST[$key])
            // remove not allowed chars
            ? $this->requestFilter->apply(trim($_REQUEST[$key]))
            : '';

        return $param;
    }

    /**
     * Get view template.
     *
     * @param string $templateName The part of relative path of template and filename without extension.
     *
     * @return Template
     */
    protected function getViewTemplate($templateName)
    {
        /** @var Template $template */
        $template = $this->objectManager->get(Template::class);
        $template->setTemplateRootPath($this->pluginPath . '/Resources/Templates/');
        $template->setPartialRootPath($this->pluginPath . '/Resources/Partials/');
        $template->setTemplate($templateName);

        // assign current action to template
        $action = $this->getAction();
        $template->assign('action', $action);

        return $template;
    }
}