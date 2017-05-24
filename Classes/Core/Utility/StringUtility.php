<?php
namespace Bfc\Core\Utility;

use Bfc\Core\Object\SingletonInterface;

class StringUtility implements SingletonInterface
{
    /**
     * Return string as CamelCase.
     *
     * @param string $str
     *
     * @return string
     */
    public static function toUpperCamelCase($str)
    {
        $str = str_replace('_', ' ', $str);
        $str = ucwords($str);

        return str_replace(' ', '', $str);
    }

    /**
     * Return string as lowerCamelCase.
     *
     * @param string $str
     *
     * @return string
     */
    public static function toLowerCamelCase($str)
    {
        $str = self::toUpperCamelCase($str);

        return self::toLowerCaseFirst($str);
    }

    /**
     * Set first char to lower case.
     *
     * @param string $str
     *
     * @return string
     */
    public static function toLowerCaseFirst($str)
    {
        $firstChar = strtolower($str[0]);

        return substr_replace($str, $firstChar, 0, 1);
    }

    /**
     * Get string as under_score.
     *
     * @param string $str
     *
     * @return string
     */
    public static function toUnderscore($str)
    {
        $str = preg_replace_callback(
            '/(^|[a-z])([A-Z])/',
            function ($m) {
                return strlen($m[1]) ? "$m[1]_$m[2]" : "$m[2]";
            },
            $str
        );

        return strtolower($str);
    }
}