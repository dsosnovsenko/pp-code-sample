<?php
namespace Bfc\Core\Object;

class Registry
{
    /** @var object[] */
    protected static $singletonInstances = [];

    /**
     * Get instance.
     *
     * @note Use SingletonInterface for singleton object.
     *
     * @param string $className
     *
     * @return mixed|null
     * @throws
     */
    public static function getInstance($className)
    {
        $object = null;

        if (!is_string($className) || empty($className)) {
            throw new \Exception('The class name must be a non empty string. ' . time());
        }

        if ($className[0] === '\\') {
            throw new \Exception('The class name "' . $className . '" must not start with a backslash. ' . time());
        }

        /*if ($className === self::class) {
            return self::createInstance(self::class);
        }*/

        /*if (class_exists($className) === false) {
            throw new \Exception('Warning: Class "' . $className . '" not found. ' . time());
        }*/

        if (isset(self::$singletonInstances[$className])) {
            return self::$singletonInstances[$className];
        }

        $arguments = func_get_args();
        array_shift($arguments);

        try {
            // Create new instance and call constructor with parameters
            $object = self::createInstance($className, $arguments);
        } catch (\Exception $exception) {
            throw $exception;
        }

        if ($object instanceof SingletonInterface) {
            self::$singletonInstances[$className] = $object;
        }

        return $object;
    }

    /**
     * Speed optimized alternative to ReflectionClass::newInstanceArgs().
     *
     * @param string $className Name of the class to instantiate
     * @param array $arguments Arguments of class construct
     *
     * @return mixed
     */
    protected static function createInstance($className, array $arguments = [])
    {
        switch (count($arguments)) {
            case 0:
                $instance = new $className();
                break;
            case 1:
                $instance = new $className($arguments[0]);
                break;
            case 2:
                $instance = new $className($arguments[0], $arguments[1]);
                break;
            case 3:
                $instance = new $className($arguments[0], $arguments[1], $arguments[2]);
                break;
            case 4:
                $instance = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                break;
            case 5:
                $instance = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
                break;
            case 6:
                $instance = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4],
                    $arguments[5]);
                break;
            case 7:
                $instance = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4],
                    $arguments[5], $arguments[6]);
                break;
            case 8:
                $instance = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4],
                    $arguments[5], $arguments[6], $arguments[7]);
                break;
            default:
                // The default case for classes with constructors that have more than 8 arguments.
                // This will fail when one of the arguments shall be passed by reference.
                $class = new \ReflectionClass($className);
                $instance = $class->newInstanceArgs($arguments);
        }

        return $instance;
    }
}