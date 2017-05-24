<?php
namespace Bfc\Core\Autoload;

/**
 * SPL Class Autoload PSR-4.
 *
 */
class ClassLoader
{
    /**
     * Class map.
     *
     * @var array $classMap
     */
    private static $classMap = [
        'Bfc\\Core\\' => [
            __DIR__ . '/../',
        ],
    ];

    /**
     * Initialize autoload.
     *
     * @param array $map
     */
    public static function initialize(array $map = [])
    {
        foreach ($map as $namespace => $path) {
            if (strlen($namespace) > 1 && !isset(self::$classMap[$namespace])) {
                // todo Check exists path
                self::$classMap[$namespace] = $path;
            }
        }

        self::register(true);
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public static function register($prepend = false)
    {
        spl_autoload_register([self::class, 'loadClass'], true, $prepend);
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     *
     * @return bool|null True if loaded, null otherwise
     */
    public static function loadClass($class)
    {
        if ($file = self::findFile($class)) {
            bfcClassLoaderFileInclude($file);

            return true;
        }

        return null;
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|false The path if found, false otherwise
     */
    public static function findFile($class)
    {
        foreach (self::$classMap as $prefix => $locations) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // move to the next registered namespace
                continue;
            }

            // get the relative class name
            $relativeClass = substr($class, $len);
            foreach ($locations as $path) {
                if (is_file($path)) {
                    $file = $path;
                } else {
                    $file = $path . str_replace('\\', '/', $relativeClass) . '.php';
                }

                if (file_exists($file)) {
                    return $file;
                }
            }
        }

        return false;
    }
}

/**
 * Isolated scope of include.
 *
 * Prevents access to $this/self from included files.
 */
if (!function_exists('bfcClassLoaderFileInclude')) {
    function bfcClassLoaderFileInclude($file) {
        include $file;
    }
}