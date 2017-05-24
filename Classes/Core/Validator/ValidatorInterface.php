<?php
namespace Bfc\Core\Validator;

interface ValidatorInterface
{
    /**
     * Checks if the given value is valid.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value);

    /**
     * Returns the options of this validator which can be specified in the constructor.
     *
     * @return array
     */
    public function getOptions();
}