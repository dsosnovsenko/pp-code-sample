<?php
namespace Bfc\Core\Filter;

interface FilterInterface
{
    /**
     * Checks if the given value is valid.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function filter($value);

    /**
     * Returns the options of this filter which can be specified in the constructor.
     *
     * @return array
     */
    public function getOptions();
}