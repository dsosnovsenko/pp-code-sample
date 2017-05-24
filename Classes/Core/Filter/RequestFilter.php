<?php
namespace Bfc\Core\Filter;

class RequestFilter extends AbstractFilter
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        // The maximal length of string, required = false
        'length' => [0, false],
    ];

    /**
     * Filter of European character from request.
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        // pattern of allowed characters for input
        $pattern = '{[^' . $this->getCharacters() . '0-9@_,:;# \*\!\.\+\-]}s';
        $value = preg_replace($pattern, '', $value);
        // remove more as 1 space
        $value = preg_replace('{(\s)+}s', ' ', $value);

        $length = $this->options['length'];
        if ($length > 0) {
            $value = substr($value, 0, $length);
        }

        $value = trim($value);

        return $value;
    }
}