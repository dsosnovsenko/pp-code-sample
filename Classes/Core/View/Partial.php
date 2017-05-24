<?php
namespace Bfc\Core\View;

/**
 * Class Partial.
 *
 * @package Bfc\Core\View
 */
class Partial
{
    /**
     * Absolute root path of templates.
     *
     * @var string $partialRootPath
     */
    protected $partialRootPath = '';

    /**
     * Absolute partial filename of plugin.
     *
     * @var string $partialFile
     */
    protected $partialFile = '';

    /**
     * Initialize object.
     * Magic method.
     */
    public function initializeObject()
    {
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
        $this->partialRootPath = $path;
    }

    /**
     * Set partial.
     *
     * @param string $partialName The part of relative path of partial and filename without extension.
     *
     * @return void
     */
    public function setPartial($partialName = 'Default')
    {
        $format = 'php';
        $this->partialFile = $this->partialRootPath . $partialName . '.' . $format;
    }

    /**
     * Render partial.
     *
     * @param string $partialName
     * @param array $arguments
     *
     * @return void
     * @throws \Exception
     */
    public function render($partialName, $arguments = [])
    {
        $this->setPartial($partialName);

        if (!file_exists($this->partialFile)) {
            throw new \Exception('Partial file "' . $this->partialFile . '" not found.');
        }

        // extract all arguments with key names in scope of partial, e.g.:
        // $arguments = ['title' => 'Mr']; it will be available in partial as variable "$title"
        extract($arguments);

        ob_start();
        include $this->partialFile;
        $partial = ob_get_contents();
        ob_end_clean();

        echo $partial;
    }
}