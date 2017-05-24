<?php
namespace Bfc\Core\Validator;

class AlphanumericValidator extends AbstractValidator
{
    /**
     * The given $value is valid if it is an alphanumeric string.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if (!is_string($value) || preg_match('/^[\pL\d]*$/u', $value) !== 1) {
            $isValid = false;
        }

        return $isValid;
    }
}