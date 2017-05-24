<?php
namespace Bfc\Core\Filter;

class NumericFilter extends AbstractFilter
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        // The maximal length of string, default 0, required = false
        'length' => [0, false],
        // Whether to allow white space characters, default not allow, required = false
        'allowWhiteSpace' => [false, false],
        // Allow custom characters (e.g.: '\.\+\-\(\)', etc.), default empty, required = false
        'allowCharacters' => ['', false],
    ];

    /**
     * Filter of European character.
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $allowCharacters = $this->options['allowCharacters'];

        $whiteSpace = $this->options['allowWhiteSpace']
            ? '\s'
            : '';

        // pattern of allowed characters for input
        $pattern = '{[^0-9' . $whiteSpace . $allowCharacters .']}s';
        $value = preg_replace($pattern, '', (string) $value);

        $length = $this->options['length'];
        if ($length > 0) {
            $value = substr($value, 0, $length);
        }

        return $value;
    }
}