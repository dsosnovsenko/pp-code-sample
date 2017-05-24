<?php
namespace Bfc\Core\Validator;

class TextValidator extends AbstractValidator
{
    /**
     * Checks if the given value is a valid text (contains no XML tags).
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if ($value !== filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)) {
            $isValid = false;
        }

        return $isValid;
    }
}