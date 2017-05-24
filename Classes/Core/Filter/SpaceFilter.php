<?php
namespace Bfc\Core\Filter;

class SpaceFilter extends AbstractFilter
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        // The maximal length of string, default 0, required = false
        'length' => [0, false],
        // Whether to allow single white space characters, default not allow, required = false
        'allowWhiteSpace' => [false, false],
    ];

    /**
     * Filter of white space characters.
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $whiteSpace = $this->options['allowWhiteSpace']
            ? ' '
            : '';

        // remove more as 1 space
        $value = preg_replace('{(\s)+}s', $whiteSpace, $value);

        $length = $this->options['length'];
        if ($length > 0) {
            $value = substr($value, 0, $length);
        }

        return $value;
    }
}