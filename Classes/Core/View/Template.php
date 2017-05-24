<?php
namespace Bfc\Core\View;

/**
 * Class Template.
 *
 * Usage in ActionController, e.g.:
 *
 * $template = $this->objectManager->get(\Bfc\Core\View\Template::class);
 * $template->setTemplateRootPath($this->pluginPath . '/Resources/Templates/');
 * $template->setPartialRootPath($this->pluginPath . '/Resources/Partials/');
 * $template->setTemplate('MyController/Default');
 * $template->assign('title', 'Mr');
 * $template->render();
 *
 * @package Bfc\Core\View
 */
class Template
{
    /**
     * Assets data.
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * @var \Bfc\Core\View\Partial
     * @inject
     */
    protected $partial = null;

    /**
     * Absolute root path of templates.
     *
     * @var string $templateRootPath
     */
    protected $templateRootPath = '';

    /**
     * Absolute template filename of plugin.
     *
     * @var string $templateFile
     */
    protected $templateFile = '';

    /**
     * Initialize object.
     * Magic method.
     */
    public function initializeObject()
    {
    }

    /**
     * Set absolute root path of templates.
     *
     * @param string $path
     *
     * @return void
     */
    public function setTemplateRootPath($path)
    {
        $this->templateRootPath = $path;
    }

    /**
     * Set absolute root path of partials.
     *
     * @param string $path
     *
     * @return void
     */
    public function setPartialRootPath($path)
    {
        $this->partial->setPartialRootPath($path);
    }

    /**
     * Set template.
     *
     * @param string $templateName The part of relative path of template and filename without extension.
     *
     * @return void
     */
    public function setTemplate($templateName = 'Default')
    {
        $format = 'php';
        $this->templateFile = $this->templateRootPath . $templateName . '.' . $format;
    }

    /**
     * Add a variable to the view data collection.
     *
     * @param string $key Key of variable
     * @param mixed $value Value of object
     *
     * @return void
     */
    public function assign($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Reset all assignations.
     */
    public function resetAssignation()
    {
        $this->data = [];
    }

    /**
     * Add multiple variables to the view data collection.
     *
     * @param array $values array in the format array(key1 => value1, key2 => value2)
     *
     * @return void
     */
    public function assignMultiple(array $values)
    {
        $this->data = array_merge($this->data, $values);
    }

    /**
     * Render and output the template.
     *
     * @return void
     */
    public function render()
    {
        echo $this->renderToString();
    }

    /**
     * Render the template to string.
     *
     * @return string
     * @throws \Exception
     */
    public function renderToString()
    {
        // Usage in $this->templateFile, e.g.:
        // $partial->render($partialName = 'MyController/Default', $arguments = ['title' => 'Mr.'])
        $partial = $this->partial;

        if (!file_exists($this->templateFile)) {
            throw new \Exception('Template file "' . $this->templateFile . '" not found.');
        }

        // extract all arguments with key names in scope of template, e.g.:
        // $arguments = ['title' => 'Mr']; it will be available in template as variable "$title"
        extract($this->data);

        ob_start();
        include $this->templateFile;
        $template = ob_get_contents();
        ob_end_clean();

        return $template;
    }
}