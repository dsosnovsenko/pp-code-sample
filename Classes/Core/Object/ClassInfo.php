<?php
namespace Bfc\Core\Object;

class ClassInfo
{
    /**
     * The info class name.
     *
     * @var string
     */
    private $className;

    /**
     * The constructor Dependencies for the class in the format:
     * [
     *   0 => [
     *     'name' => <arg name>, the name of argument
     *     'dependency' => <classname>, the type of the argument if the argument is a class
     *     'defaultValue' => <mixed>, the default value if the argument is optional
     *   ],
     * ]
     *
     * @var array
     */
    private $constructorArguments;

    /**
     * All setter injections in the format:
     * [<nameOfMethod> => <classNameToInject>]
     *
     * @var array
     */
    private $injectMethods;

    /**
     * All setter injections in the format:
     * [<nameOfProperty> => <classNameToInject>]
     *
     * @var array
     */
    private $injectProperties;

    /**
     * Indicates if the class is a singleton.
     *
     * @var bool
     */
    private $singleton = false;

    /**
     * Indicates if the class has the method initializeObject.
     *
     * @var bool
     */
    private $initializable = false;

    /**
     * @param string $className
     * @param array $constructorArguments
     * @param array $injectMethods
     * @param array $injectProperties
     * @param bool $isSingleton
     * @param bool $isInitializable
     */
    public function __construct(
        $className,
        array $constructorArguments,
        array $injectMethods,
        array $injectProperties = [],
        $isSingleton = false,
        $isInitializable = false
    ) {
        $this->className = $className;
        $this->constructorArguments = $constructorArguments;
        $this->injectMethods = $injectMethods;
        $this->injectProperties = $injectProperties;
        $this->singleton = $isSingleton;
        $this->initializable = $isInitializable;
    }

    /**
     * Gets the class name passed to constructor.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get arguments passed to constructor.
     *
     * @return array
     */
    public function getConstructorArguments()
    {
        return $this->constructorArguments;
    }

    /**
     * Returns an array with the inject methods.
     *
     * @return array
     */
    public function getInjectMethods()
    {
        return $this->injectMethods;
    }

    /**
     * Returns an array with the inject properties.
     *
     * @return array
     */
    public function getInjectProperties()
    {
        return $this->injectProperties;
    }

    /**
     * Asserts if the class is a singleton or not.
     *
     * @return bool
     */
    public function isSingleton()
    {
        return $this->singleton;
    }

    /**
     * Asserts if the class is initializable with initializeObject.
     *
     * @return bool
     */
    public function isInitializable()
    {
        return $this->initializable;
    }

    /**
     * Asserts if the class has Dependency Injection methods.
     *
     * @return bool
     */
    public function hasInjectMethods()
    {
        return !empty($this->injectMethods);
    }

    /**
     * @return bool
     */
    public function hasInjectProperties()
    {
        return !empty($this->injectProperties);
    }
}