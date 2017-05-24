<?php
namespace Bfc\Core\Validator;

class StringLengthValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'minimum' => [0, 'Minimum length for a valid string', 'integer'],
        'maximum' => [PHP_INT_MAX, 'Maximum length for a valid string', 'integer']
    ];

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if (!is_string($value)) {

            return false;
        }

        $stringLength = strlen($value);

        if ($stringLength < $this->options['minimum']) {
            $isValid = false;
        }
        if ($stringLength > $this->options['maximum']) {
            $isValid = false;
        }

        return $isValid;
    }
}