<?php
namespace Bfc\Core\Object;

/**
 * Class ObjectManager.
 * This class provide dependency injection.
 *
 * @package Bfc\Core
 */
class ObjectManager implements SingletonInterface
{
    /** @var object[] */
    protected $singletonInstances = [];

    /** @var object[] */
    protected $classInfoCache = [];

    /** @var array The array of instances that currently being built. */
    protected $currentInstances = [];

    /**
     * Get initialized instance.
     *
     * @note Use SingletonInterface for singleton object.
     *
     * @param string $className
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($className)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        $this->currentInstances = [];

        try {
            $instance = $this->getInstance($className, $arguments);
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $instance;
    }

    /**
     * Get instance for internal getting.
     *
     * @param string $className
     * @param array $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getInstance($className, array $arguments = [])
    {
        if ($className === ObjectManager::class) {
            return $this;
        }

        if (isset($this->singletonInstances[$className])) {
            if (!empty($arguments)) {
                throw new \Exception('Object "' . $className . '" fetched from singleton cache, thus, explicit constructor arguments are not allowed. ' . time());
            }
            return $this->singletonInstances[$className];
        }

        $classInfo = $this->getClassInfo($className);
        $isSingleton = $classInfo->isSingleton();

        if (!$isSingleton) {
            if (array_key_exists($className, $this->currentInstances) !== false) {
                throw new \Exception('Cyclic dependency in prototype object, for class "' . $className . '". ' . time());
            }
            $this->currentInstances[$className] = true;
        }

        // recovery $arguments for function func_get_args() in Registry::getInstance()
        array_unshift($arguments, $className);
        // Create new instance and call constructor with parameters
        $object = call_user_func_array([Registry::class, 'getInstance'], $arguments);

        $this->injectDependencies($object, $classInfo);

        if ($classInfo->isInitializable() && is_callable([$object, 'initializeObject'])) {
            $object->initializeObject();
        }

        if ($isSingleton) {
            $this->singletonInstances[$className] = $object;
        } else {
            unset($this->currentInstances[$className]);
        }

        return $object;
    }

    /**
     * Inject dependencies into $instance.
     *
     * @param object $instance The given instance into will be inject a dependencies.
     * @param ClassInfo $classInfo
     *
     * @return void
     */
    protected function injectDependencies($instance, ClassInfo $classInfo)
    {
        if (!$classInfo->hasInjectMethods() && !$classInfo->hasInjectProperties()) {
            return;
        }

        foreach ($classInfo->getInjectMethods() as $injectMethodName => $injectClassName) {
            $injectInstance = $this->getInstance($injectClassName);
            if ($classInfo->isSingleton() && !$injectInstance instanceof SingletonInterface) {
                // @todo save to log ('The singleton "' . $classInfo->getClassName() . '" needs a prototype in "' . $injectMethodName . '". This is often a bad code smell; often you rather want to inject a singleton.');
            }
            if (is_callable([$instance, $injectMethodName])) {
                $instance->{$injectMethodName}($injectInstance);
            }
        }

        $classProperties = $classInfo->getInjectProperties();

        foreach ($classProperties as $injectPropertyName => $injectClassName) {
            $injectInstance = $this->getInstance($injectClassName);
            if ($classInfo->isSingleton() && !$injectInstance instanceof SingletonInterface) {
                // @todo save to log ('The singleton "' . $classInfo->getClassName() . '" needs a prototype in "' . $injectPropertyName . '". This is often a bad code smell; often you rather want to inject a singleton.');
            }
            $propertyReflection = Registry::getInstance(\ReflectionProperty::class, $instance, $injectPropertyName);
            $propertyReflection->setAccessible(true);
            $propertyReflection->setValue($instance, $injectInstance);
        }
    }

    /**
     * Build a list of properties to be injected for the given class.
     *
     * @param \ReflectionClass $instance
     *
     * @return array [nameOfInjectProperty => nameOfClassToBeInjected]
     */
    private function getInjectProperties(\ReflectionClass $instance)
    {
        $result = [];
        $reflectionProperties = $instance->getProperties();
        if (!is_array($reflectionProperties)) {
            return $result;
        }

        $instanceClassName = $instance->getName();

        foreach ($reflectionProperties as $reflectionProperty) {
            $reflectedProperty = Registry::getInstance(\ReflectionProperty::class, $instanceClassName, $reflectionProperty->getName());
            $injectClassName = $this->getInjectClassName($reflectedProperty);
            $propertyName = $reflectedProperty->getName();
            if ($injectClassName && $propertyName !== 'settings') {
                $result[$propertyName] = $injectClassName;
            }
        }

        return $result;
    }

    /**
     * Build a list of constructor arguments.
     *
     * @param \ReflectionClass $instance
     *
     * @return array of parameter infos for constructor
     */
    private function getConstructorArguments(\ReflectionClass $instance)
    {
        $reflectionMethod = $instance->getConstructor();
        if (!is_object($reflectionMethod)) {
            return [];
        }

        $result = [];
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            /* @var $reflectionParameter \ReflectionParameter */
            $info = [];
            $info['name'] = $reflectionParameter->getName();
            if ($reflectionParameter->getClass()) {
                $info['dependency'] = $reflectionParameter->getClass()->getName();
            }

            try {
                $info['defaultValue'] = $reflectionParameter->getDefaultValue();
            } catch (\ReflectionException $exception) {
                // do nothing
            }

            $result[] = $info;
        }

        return $result;
    }

    /**
     * Get cacheable class info for the className.
     *
     * @param string $className
     *
     * @return ClassInfo
     */
    private function getClassInfo($className)
    {
        $classNameHash = md5($className);

        if (!isset($this->classInfoCache[$classNameHash])) {
            $classInfo = $this->buildClassInfo($className);
            $this->classInfoCache[$classNameHash] = $classInfo;
        } else {
            $classInfo = $this->classInfoCache[$classNameHash];
        }

        return $classInfo;
    }

    /**
     * Builds a ClassInfo object for the given class name, using reflection.
     *
     * @param string $className The class name for build the class info.
     *
     * @return ClassInfo The class info object.
     * @throws \Exception
     */
    public function buildClassInfo($className)
    {
        if ($className === 'DateTime') {
            return new ClassInfo($className, [], [], [], false, false);
        }

        try {
            $instance = new \ReflectionClass($className);
        } catch (\Exception $exception) {
            throw new \Exception('Could not analyse class: "' . $className . '" maybe not loaded or no autoloader? ' . time());
        }
        $constructorArguments = $this->getConstructorArguments($instance);
        $injectMethods = $this->getInjectMethods($instance);
        $injectProperties = $this->getInjectProperties($instance);
        $isSingleton = $this->isSingleton($className);
        $isInitializable = method_exists($className, 'initializeObject');

        return new ClassInfo($className, $constructorArguments, $injectMethods, $injectProperties, $isSingleton, $isInitializable);
    }

    /**
     * Get injected class name of property.
     *
     * @notice Get the class name from line of docBlock like this: @var \ClassName $variableName
     *
     * @param \ReflectionProperty $reflectedProperty
     *
     * @return string
     */
    protected function getInjectClassName(\ReflectionProperty $reflectedProperty)
    {
        $varValues = [];
        $isInjection = false;
        $docComment = $reflectedProperty->getDocComment();
        $lines = explode("\n", $docComment);

        foreach ($lines as $line) {
            if (stripos($line, '@inject') !== false) {
                $isInjection = true;
            } elseif (strpos($line, '@var') !== false) {
                // remove @var
                list(, $varValue) = explode('@var', $line);
                $varValue = trim($varValue);
                // split to class name and variable name
                // $varName is reserved und yet unused
                list($className, $varName) = explode(' ', $varValue);
                $className = trim($className);
                $varValues[] = ltrim($className, '\\');
            }
        }

        if ($isInjection && count($varValues) === 1) {
            return $varValues[0];
        }

        return '';
    }

    /**
     * Build a list of inject methods for the given class.
     *
     * @param \ReflectionClass $instance
     *
     * @return array [nameOfInjectMethod => nameOfClassToBeInjected]
     * @throws \Exception
     */
    private function getInjectMethods(\ReflectionClass $instance)
    {
        $result = [];
        $reflectionMethods = $instance->getMethods();

        if (!is_array($reflectionMethods)) {
            return $result;
        }

        foreach ($reflectionMethods as $reflectionMethod) {
            if ($reflectionMethod->isPublic() && $this->isInjectMethod($reflectionMethod->getName())) {
                $reflectionParameter = $reflectionMethod->getParameters();
                if (isset($reflectionParameter[0])) {
                    if (!$reflectionParameter[0]->getClass()) {
                        throw new \Exception('Method "' . $reflectionMethod->getName() . '" of class "' . $instance->getName() . '" is marked as setter for Dependency Injection, but does not have a type annotation');
                    }
                    $result[$reflectionMethod->getName()] = $reflectionParameter[0]->getClass()->getName();
                }
            }
        }

        return $result;
    }

    /**
     * Checks if given method can be used for injection.
     *
     * @param string $methodName
     *
     * @return bool
     */
    private function isInjectMethod($methodName)
    {
        if (substr($methodName, 0, 6) === 'inject'
            && $methodName[6] === strtoupper($methodName[6])
            && $methodName !== 'injectSettings'
        ) {

            return true;
        }

        return false;
    }

    /**
     * Determine whether a class is a singleton.
     *
     * @param string $className
     *
     * @return bool
     */
    public function isSingleton($className)
    {
        return in_array(SingletonInterface::class, class_implements($className));
    }
}