<?php
namespace Bfc\Core\Filter;

class StripesQuotesFilter extends AbstractFilter
{
    /**
     * @var array
     */
    protected $supportedOptions = [
    ];

    /**
     * Filter of stripes quotes from the variable value.
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $value = str_replace('"', '', $value);

        return $value;
    }
}